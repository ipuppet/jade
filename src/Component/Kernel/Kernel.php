<?php


namespace Jade\Component\Kernel;


use Jade\Component\Http\Request;
use Jade\Component\Http\Response;
use Jade\Component\Kernel\ConfigLoader\Exception\ConfigLoaderException;
use Jade\Component\Kernel\ConfigLoader\JsonParser;
use Jade\Component\Kernel\ConfigLoader\Loader;
use Jade\Component\Kernel\Controller\ControllerResolver;
use Jade\Component\Logger\Logger;
use Jade\Component\Router\Exception\MatcherNoneRequestException;
use Jade\Component\Router\RouteContainer;
use Jade\Component\Router\Router;
use Jade\Foundation\Path\Exception\PathException;
use Jade\Foundation\Path\Path;
use Jade\Foundation\Path\PathInterface;

abstract class Kernel
{
    /**
     * @var Loader
     */
    private $configLoader;

    /**
     * 获取缓存目录
     * @return PathInterface
     * @throws PathException
     */
    public function getCacheDir(): PathInterface
    {
        return $this->createPath($this->getRootDir() . "/var/cache");
    }

    /**
     * 获取日志目录
     * @return PathInterface
     * @throws PathException
     */
    public function getLogDir(): PathInterface
    {
        return $this->createPath($this->getRootDir() . "/var/log");
    }

    /**
     * 获取项目根目录
     * @return PathInterface
     * @throws PathException
     */
    public function getRootDir(): PathInterface
    {
        return $this->createPath(dirname(__DIR__));
    }

    /**
     * @param string $path
     * @return PathInterface
     * @throws PathException
     */
    public function createPath(string $path = ''): PathInterface
    {
        try {
            return new Path($path);
        } catch (PathException $e) {
            throw $e;
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws PathException
     * @throws MatcherNoneRequestException
     * @throws ConfigLoaderException
     */
    public function handle(Request $request): Response
    {
        $request->headers->set('X-Php-Ob-Level', ob_get_level());
        $logger = new Logger();
        try {
            $logger->setName('ControllerResolver')->setOutput($this->getLogDir());
            $controllerResolver = new ControllerResolver($logger);
        } catch (PathException $e) {
            throw $e;
        }
        $logger->setName('Router')->setOutput($this->getLogDir());

        $router = new Router();
        $router->setRequest($request)
            ->setLogger($logger)
            ->setRouteContainer($this->getRouteContainer())
            ->setKernel($this);

        if ($router->matchAll()) {
            $controller = $controllerResolver->getController($request);
            //调用
            $response = call_user_func_array($controller, $request->request->all());
            if ($response instanceof Response) {
                return $response;
            }
        }
        $reason = $router->getReason();
        $response = new Response($reason->getContent(), $reason->getHttpStatus());
        return $response;
    }

    /**
     * @return RouteContainer
     * @throws PathException
     * @throws ConfigLoaderException
     */
    private function getRouteContainer(): RouteContainer
    {
        $path = $this->createPath($this->getRootDir()->after($this->createPath('/app/config')));
        $loader = $this->getConfigLoader()
            ->setPath($path)
            ->setName('routes')
            ->setParser(new JsonParser())
            ->loadFromFile();
        $routes = $loader->all();
        return RouteContainer::createByArray($routes);
    }

    /**
     * @return Loader
     */
    public function getConfigLoader(): Loader
    {
        if ($this->configLoader === null) {
            $this->configLoader = new Loader();
        }
        return $this->configLoader;
    }
}