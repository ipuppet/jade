<?php


namespace Ipuppet\Jade\Component\Kernel;


use Ipuppet\Jade\Component\Http\Request;
use Ipuppet\Jade\Component\Http\Response;
use Ipuppet\Jade\Component\Kernel\Config\Config;
use Ipuppet\Jade\Component\Kernel\Config\ConfigLoader;
use Ipuppet\Jade\Component\Kernel\Controller\ControllerResolver;
use Ipuppet\Jade\Component\Logger\Logger;
use Ipuppet\Jade\Component\Router\Exception\NoMatcherException;
use Ipuppet\Jade\Component\Router\Matcher\MatchByRegexPath;
use Ipuppet\Jade\Component\Router\RouteContainer;
use Ipuppet\Jade\Component\Router\Router;
use Ipuppet\Jade\Foundation\Parser\JsonParser;
use Ipuppet\Jade\Foundation\Path\Exception\PathException;
use Ipuppet\Jade\Foundation\Path\Path;
use Ipuppet\Jade\Foundation\Path\PathInterface;
use ReflectionException;

abstract class Kernel
{
    private bool $isLogAccessError = false;
    /**
     * @var ?ConfigLoader
     */
    private ?ConfigLoader $configLoader = null;

    /**
     * @var ?PathInterface
     */
    protected ?PathInterface $cachePath = null;

    /**
     * @var ?PathInterface
     */
    protected ?PathInterface $logPath = null;

    /**
     * @var ?PathInterface
     */
    protected ?PathInterface $rootPath = null;
    /**
     * @var Config
     */
    private Config $config;

    /**
     * Kernel constructor.
     * @throws PathException
     */
    public function __construct()
    {
        $this->config = $this->getConfigLoader()->setName('config')->loadFromFile();
    }

    /**
     * 获取缓存目录
     * @return PathInterface
     * @throws PathException
     */
    public function getCachePath(): PathInterface
    {
        if ($this->cachePath === null) {
            $this->cachePath = new Path($this->getRootPath());
            $this->cachePath->after('/var/cache');
        }
        return $this->cachePath;
    }

    /**
     * 获取日志目录
     * @return PathInterface
     * @throws PathException
     */
    public function getLogPath(): PathInterface
    {
        if ($this->logPath === null) {
            $this->logPath = new Path($this->getRootPath());
            $this->logPath->after('/var/log');
        }
        return $this->logPath;
    }

    /**
     * 获取项目根目录
     * @return PathInterface
     * @throws PathException
     */
    public function getRootPath(): PathInterface
    {
        if ($this->rootPath === null) {
            $path = substr(__DIR__, 0, strripos(__DIR__, 'vendor') - 1);
            $this->rootPath = new Path($path);
        }
        return $this->rootPath;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws PathException
     * @throws NoMatcherException
     * @throws ReflectionException
     */
    public function handle(Request $request): Response
    {
        $request->headers->set('X-Php-Ob-Level', ob_get_level());
        $logger = new Logger();
        // 实例化ControllerResolver
        $logger->setName('ControllerResolver')->setOutput($this->getLogPath());
        $controllerResolver = new ControllerResolver($logger);
        // 实例化Router对象
        $logger->setName('Router')->setOutput($this->getLogPath());
        $router = new Router();
        $matcher = new MatchByRegexPath();
        $router->setRequest($request)
            ->setLogger($logger)
            ->setRouteContainer($this->getRouteContainer())
            ->setMatcher($matcher);
        // 获取Config对象
        $config = new Config();
        if ($this->config->has('errorResponse')) $config->add($this->config->get('errorResponse'));
        $config->add(['rootPath' => $this->getRootPath()]);
        $router->setConfig($config);
        // 开始匹配路由
        if ($router->matchAll()) {
            $request = $router->getRequest();
            $controller = $controllerResolver->getController($request);
            // 判断配置文件内是否有跨域配置，若有则注入到控制器中
            if ($this->config->has('cors') && !empty($this->config->get('cors'))) {
                call_user_func([$controller[0], 'setCorsConfig'], new Config($this->config->get('cors')));
            }
            // 整理参数顺序，按照方法签名对齐
            $parameters = $controllerResolver->sortRequestParameters($controller, $request);
            // 调用控制器中对应的方法并获得Response
            $response = call_user_func_array($controller, $parameters);
            if ($response instanceof Response) {
                // 必须等待$response取到值，因为cors设置在控制器中可能被修改
                $isPassCorsCheck = call_user_func([$controller[0], 'checkCors']);
                if ($request->getMethod() === 'OPTIONS') { // 对OPTIONS请求进行处理
                    return Response::create('', $isPassCorsCheck ? Response::HTTP_204 : Response::HTTP_400);
                }
                return $response;
            } else {
                $logger->error('The return value of Controller must need instance of Response.');
            }
        }
        // 响应错误信息
        $reason = $router->getReason($this->config->get('isLogAccessError', false));
        return new Response($reason->getContent(), $reason->getHttpStatus());
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

    /**
     * @return ConfigLoader
     * @throws PathException
     */
    public function getConfigLoader(): ConfigLoader
    {
        if ($this->configLoader === null) {
            $this->configLoader = new ConfigLoader();
            $path = new Path($this->getRootPath());
            $path->after('/config');
            $this->configLoader->setPath($path)->setParser(new JsonParser());
        }
        return $this->configLoader;
    }
}