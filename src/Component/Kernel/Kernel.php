<?php


namespace Zimings\Jade\Component\Kernel;


use Zimings\Jade\Component\Http\Request;
use Zimings\Jade\Component\Http\Response;
use Zimings\Jade\Foundation\Parser\JsonParser;
use Zimings\Jade\Component\Kernel\Config\ConfigLoader;
use Zimings\Jade\Component\Kernel\Controller\ControllerResolver;
use Zimings\Jade\Component\Logger\Logger;
use Zimings\Jade\Component\Router\Exception\NoMatcherException;
use Zimings\Jade\Component\Router\Matcher\MatchByRegexPath;
use Zimings\Jade\Component\Router\RouteContainer;
use Zimings\Jade\Component\Router\Router;
use Zimings\Jade\Foundation\Path\Exception\PathException;
use Zimings\Jade\Foundation\Path\Path;
use Zimings\Jade\Foundation\Path\PathInterface;

abstract class Kernel
{
    private $isLogAccessError = false;
    /**
     * @var ConfigLoader
     */
    private $configLoader;

    /**
     * @var PathInterface
     */
    protected $cachePath;

    /**
     * @var PathInterface
     */
    protected $logPath;

    /**
     * @var PathInterface
     */
    protected $rootPath;

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
            $this->rootPath = new Path(dirname(__DIR__));
        }
        return $this->rootPath;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws PathException
     * @throws NoMatcherException
     */
    public function handle(Request $request): Response
    {
        $request->headers->set('X-Php-Ob-Level', ob_get_level());
        $logger = new Logger();
        //实例化ControllerResolver
        $logger->setName('ControllerResolver')->setOutput($this->getLogPath());
        $controllerResolver = new ControllerResolver($logger);
        //实例化Router对象
        $logger->setName('Router')->setOutput($this->getLogPath());
        $router = new Router();
        $matcher = new MatchByRegexPath();
        $router->setRequest($request)
            ->setLogger($logger)
            ->setRouteContainer($this->getRouteContainer())
            ->setMatcher($matcher);
        //获取Config对象
        $config = $this->getConfigLoader()->setName('response')->loadFromFile();
        //如果加载成功则向Router中传递
        if ($config !== null) {
            $config->add(['root_dir' => $this->getRootPath()]);
            $router->setConfig($config);
        }
        //开始匹配路由
        if ($router->matchAll()) {
            $request = $router->getRequest();
            $controller = $controllerResolver->getController($request);
            //调用
            $response = call_user_func_array($controller, $request->request->all());
            if ($response instanceof Response) {
                return $response;
            }
        }
        //响应错误信息
        $reason = $router->getReason();
        $response = new Response($reason->getContent(), $reason->getHttpStatus());
        if ($this->isLogAccessError) {
            //此时已经是Router日志
            $logger->error("Access error '{$request->getPathInfo()}' {$reason->getDescription()}");
        }
        return $response;
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
            ->all();
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
            $path->after('/app/config');
            $this->configLoader->setPath($path)->setParser(new JsonParser());
            $this->loadDefaultConfig($this->configLoader);
        }
        return $this->configLoader;
    }

    /**
     * 加载框架默认配置，默认认为所有配置项到保存在config文件中
     * @param ConfigLoader $configLoader
     */
    private function loadDefaultConfig(ConfigLoader $configLoader)
    {
        $config = $configLoader->setName('config')->loadFromFile();
        if ($config->has('logAccessError')) {
            $this->isLogAccessError = $config->get('logAccessError');
        }
    }
}