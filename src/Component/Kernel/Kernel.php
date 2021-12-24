<?php


namespace Ipuppet\Jade\Component\Kernel;


use Exception;
use TypeError;
use Ipuppet\Jade\Component\Http\Request;
use Ipuppet\Jade\Component\Http\Response;
use Ipuppet\Jade\Component\Logger\Logger;
use Ipuppet\Jade\Component\Router\Exception\NoMatcherException;
use Ipuppet\Jade\Component\Router\Matcher\MatchByRegexPath;
use Ipuppet\Jade\Component\Router\RouteContainer;
use Ipuppet\Jade\Component\Router\Router;
use Ipuppet\Jade\Component\Parser\JsonParser;
use Ipuppet\Jade\Component\Path\Exception\PathException;
use Ipuppet\Jade\Component\Path\Path;
use Ipuppet\Jade\Component\Path\PathInterface;
use Ipuppet\Jade\Component\Parameter\Parameter;
use Ipuppet\Jade\Component\Parameter\ParameterInterface;
use ReflectionException;

abstract class Kernel
{
    private static Kernel $instance;
    /**
     * @var ?ConfigLoader
     */
    private ?ConfigLoader $configLoader;
    /**
     * @var ParameterInterface
     */
    private ?ParameterInterface $config;
    /**
     * @var ?PathInterface
     */
    protected ?PathInterface $rootPath;
    /**
     * @var ?PathInterface
     */
    protected ?PathInterface $cachePathl;
    /**
     * @var ?PathInterface
     */
    protected ?PathInterface $storagePath;
    /**
     * @var ?PathInterface
     */
    protected ?PathInterface $logPath;

    /**
     * Kernel constructor.
     * @throws PathException
     */
    public function __construct()
    {
        $this->config = $this->getConfigLoader()->setName('config')->loadFromFile();
        $this->config->add(['rootPath' => $this->getRootPath()]);
    }

    public static function getInstance(): static
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return ConfigLoader
     * @throws PathException
     */
    public function getConfigLoader(): ConfigLoader
    {
        if (!isset($this->configLoader)) {
            $this->configLoader = new ConfigLoader();
            $path = new Path($this->getRootPath());
            $path->after('/config');
            $this->configLoader->setPath($path)->setParser(new JsonParser());
        }
        return $this->configLoader;
    }

    public function getConfig(string $key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    /**
     * 获取项目根目录
     * @return PathInterface
     * @throws PathException
     */
    public function getRootPath(): PathInterface
    {
        if (!isset($this->rootPath)) {
            $path = substr(__DIR__, 0, strripos(__DIR__, 'vendor') - 1);
            $this->rootPath = new Path($path);
        }
        return $this->rootPath;
    }

    /**
     * 获取缓存目录
     * @return PathInterface
     * @throws PathException
     */
    public function getCachePath(): PathInterface
    {
        if (!isset($this->cachePath)) {
            $this->cachePath = new Path($this->getRootPath());
            $this->cachePath->after('/var/cache');
        }
        return $this->cachePath;
    }

    /**
     * 获取暂存文件目录
     * @return PathInterface
     * @throws PathException
     */
    public function getStoragePath(): PathInterface
    {
        if (!isset($this->storagePath)) {
            $this->storagePath = new Path($this->getRootPath());
            $this->storagePath->after('/var/storage');
        }
        return $this->storagePath;
    }

    /**
     * 获取日志目录
     * @return PathInterface
     * @throws PathException
     */
    public function getLogPath(): PathInterface
    {
        if (!isset($this->logPath)) {
            $this->logPath = new Path($this->getRootPath());
            $this->logPath->after('/var/log');
        }
        return $this->logPath;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws PathException
     * @throws NoMatcherException
     * @throws ReflectionException
     * @throws Exception
     */
    public function handle(Request $request): Response
    {
        $request->headers->set('X-Php-Ob-Level', ob_get_level());
        $logger = new Logger();
        // 实例化 ControllerResolver
        $logger->setName('ControllerResolver')->setOutput($this->getLogPath());
        $controllerResolver = new ControllerResolver($logger);
        // 实例化 Router 对象
        $logger->setName('Router')->setOutput($this->getLogPath());
        $router = new Router();
        $matcher = new MatchByRegexPath($this->config->get('routerStrictMode', false));
        $router->setRequest($request)
            ->setLogger($logger)
            ->setRouteContainer($this->getRouteContainer())
            ->setMatcher($matcher->setLogger($logger));
        $router->setConfig($this->config);
        // 开始匹配路由
        if ($router->matchAll()) {
            $request = $router->getRequest();
            try {
                $controller = $controllerResolver->getController($this, $request);
            } catch (Exception $error) {
                $response = Response::create('', Response::HTTP_400);
                $response->send();
                $logger->setName('ControllerResolver')->setOutput($this->getLogPath());
                $logger->error((string)$error);
                die($this->config->get('debug', false) ? (string)$error : '');
            }
            // 判断配置文件内是否有跨域配置，若有则注入到控制器中
            if ($this->config->has('cors') && !empty($this->config->get('cors'))) {
                call_user_func([$controller[0], 'setCorsConfig'], new Parameter($this->config->get('cors')));
            }
            // 验证是否可以跨域
            $isPassCorsCheck = call_user_func([$controller[0], 'checkCors']);
            if ($request->getMethod() === 'OPTIONS') { // 对 OPTIONS 请求进行处理
                return Response::create('', $isPassCorsCheck ? Response::HTTP_204 : Response::HTTP_400);
            }
            // 判断是否在控制器之前返回响应
            if (call_user_func([$controller[0], 'isResponseBeforeController'])) {
                return call_user_func([$controller[0], 'getResponse']);
            }
            // 整理参数顺序，按照方法签名对齐
            $parameters = $controllerResolver->sortRequestParameters($controller, $request);
            // 调用控制器中对应的方法并获得 Response
            try {
                $response = call_user_func_array($controller, $parameters);
            } catch (TypeError $error) {
                $logger->info($error->getMessage());
                return Response::create('Invalid parameter.', Response::HTTP_400);
            }
            if ($response instanceof Response) {
                return $response;
            } else {
                $logger->error('The return value of Controller must need instance of Response.');
            }
        }
        // 响应错误信息
        $reason = $router->getReason($this->config->get('logAccessError', false));
        return Response::create($reason->getContent(), $reason->getHttpStatus());
    }

    /**
     * @return RouteContainer
     * @throws PathException
     */
    private function getRouteContainer(): RouteContainer
    {
        $routes = $this->getConfigLoader()
            ->setName('routes')
            ->setParser(new JsonParser())
            ->loadFromFile()
            ->toArray();
        return RouteContainer::createByArray($routes);
    }
}
