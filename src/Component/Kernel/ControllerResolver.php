<?php


namespace Ipuppet\Jade\Component\Kernel;


use InvalidArgumentException;
use Ipuppet\Jade\Component\Http\Request;
use Ipuppet\Jade\Component\Kernel\Kernel;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class ControllerResolver
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @return array|callable|false|object|string
     * @throws ReflectionException
     */
    public function getController(Kernel $kernel, Request $request): callable|object|bool|array|string
    {
        if (!$controller = $request->attributes->get('controller')) {
            $this->logger?->warning('Unable to look for the controller as the "controller" parameter is missing.');
            return false;
        }
        if (is_array($controller)) {
            return $controller;
        }
        if (is_object($controller)) {
            if (method_exists($controller, '__invoke')) {
                return $controller;
            }
            throw new InvalidArgumentException(sprintf('Controller "%s" for URI "%s" is not callable.', get_class($controller), $request->getPathInfo()));
        }
        if (!str_contains($controller, ':')) {
            if (method_exists($controller, '__invoke')) {
                return $this->instantiateController($controller, $kernel, $request);
            } elseif (function_exists($controller)) {
                return $controller;
            }
        }
        $callable = $this->createController($controller, $kernel, $request);
        if (!is_callable($callable)) {
            throw new InvalidArgumentException(sprintf('The controller for URI "%s" is not callable. %s', $request->getPathInfo(), $this->getControllerError($callable)));
        }
        return $callable;
    }

    /**
     * @param $class
     * @param $request
     * @return object
     * @throws ReflectionException
     */
    protected function instantiateController($class, $kernel, $request): object
    {
        $reflectionClass = new ReflectionClass($class);
        $constructor = $reflectionClass->getConstructor();
        if ($constructor !== null) {
            $parameters = $constructor->getParameters();
            $result = [];
            foreach ($parameters as $parameter) {
                if ('Ipuppet\Jade\Component\Kernel\Kernel' === (string)$parameter->getType()) {
                    $result[$parameter->getPosition()] = $kernel;
                } elseif ('Ipuppet\Jade\Component\Http\Request' === (string)$parameter->getType()) {
                    $result[$parameter->getPosition()] = $request;
                } else {
                    $result[$parameter->getPosition()] = $request->get($parameter->getName());
                }
            }
            return $reflectionClass->newInstanceArgs($result);
        }
        return $reflectionClass->newInstanceArgs();
    }

    /**
     * @param $controller
     * @param $request
     * @return array
     * @throws ReflectionException
     */
    protected function createController($controller, $kernel, $request): array
    {
        if (!str_contains($controller, '::')) {
            throw new InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }
        list($class, $method) = explode('::', $controller, 2);
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }
        return array($this->instantiateController($class, $kernel, $request), $method);
    }

    private function getControllerError($callable): string
    {
        if (is_string($callable)) {
            if (str_contains($callable, '::')) {
                $callable = explode('::', $callable);
            }
            if (class_exists($callable) && !method_exists($callable, '__invoke')) {
                return sprintf('Class "%s" does not have a method "__invoke".', $callable);
            }
            if (!function_exists($callable)) {
                return sprintf('Function "%s" does not exist.', $callable);
            }
        }
        if (!is_array($callable)) {
            return sprintf('Invalid type for controller given, expected string or array, got "%s".', gettype($callable));
        }
        if (2 !== count($callable)) {
            return 'Invalid format for controller, expected array(controller, method) or controller::method.';
        }
        list($controller, $method) = $callable;
        if (is_string($controller) && !class_exists($controller)) {
            return sprintf('Class "%s" does not exist.', $controller);
        }
        $className = is_object($controller) ? get_class($controller) : $controller;
        if (method_exists($controller, $method)) {
            return sprintf('Method "%s" on class "%s" should be public and non-abstract.', $method, $className);
        }
        $collection = get_class_methods($controller);
        $alternatives = array();
        foreach ($collection as $item) {
            $lev = levenshtein($method, $item);
            if ($lev <= strlen($method) / 3 || str_contains($item, $method)) {
                $alternatives[] = $item;
            }
        }
        asort($alternatives);
        $message = sprintf('Expected method "%s" on class "%s"', $method, $className);
        if (count($alternatives) > 0) {
            $message .= sprintf(', did you mean "%s"?', implode('", "', $alternatives));
        } else {
            $message .= sprintf('. Available methods: "%s".', implode('", "', $collection));
        }
        return $message;
    }

    /**
     * 通过反射按照控制器的参数顺序排序
     * @param array $controller
     * @param Request $request
     * @return array
     * @throws ReflectionException
     */
    public function sortRequestParameters(array $controller, Request $request): array
    {
        // $global 定义通用参数，如获取 request 对象等
        $global = [
            'request' => ['type' => 'Ipuppet\Jade\Component\Http\Request', 'value' => $request],
        ];
        $method = new ReflectionMethod($controller[0], $controller[1]);
        $parameters = $method->getParameters();
        $result = [];
        foreach ($parameters as $parameter) {
            // 如果控制器方法存在网络请求没有的参数则去$global中寻找
            if (
                !$request->has($parameter->getName()) && // request 中不存在同名参数
                isset($global[$parameter->getName()]) && // global 中有定义
                $global[$parameter->getName()]['type'] == (string)$parameter->getType()
            ) {
                $result[$parameter->getPosition()] = $global[$parameter->getName()]['value'];
            } else {
                $default = null;
                try {
                    $default = $parameter->getDefaultValue();
                } catch (\Throwable $th) {
                }
                $result[$parameter->getPosition()] = $request->get($parameter->getName(), $default);
            }
        }
        return $result;
    }
}
