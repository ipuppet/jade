<?php


namespace Ipuppet\Jade\Component\Router;


use Exception;
use Ipuppet\Jade\Component\Http\Request;
use Ipuppet\Jade\Component\Kernel\Config\Config;
use Ipuppet\Jade\Component\Router\Exception\NoMatcherException;
use Ipuppet\Jade\Component\Router\Matcher\MatcherInterface;
use Ipuppet\Jade\Component\Router\Reason\HostNotAllow;
use Ipuppet\Jade\Component\Router\Reason\MethodNotAllow;
use Ipuppet\Jade\Component\Router\Reason\ReasonInterface;
use Ipuppet\Jade\Component\Router\Reason\NoMatch;
use Psr\Log\LoggerInterface;

class Router
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RouteContainer
     */
    private $routeContainer;

    /**
     * @var ReasonInterface
     */
    private $reason;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var MatcherInterface
     */
    private $matcher;

    public function __construct(Request $request = null, RouteContainer $routeContainer = null)
    {
        $this->request = $request;
        $this->routeContainer = $routeContainer;
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param RouteContainer $routeContainer
     * @return $this
     */
    public function setRouteContainer(RouteContainer $routeContainer)
    {
        $this->routeContainer = $routeContainer;
        return $this;
    }

    /**
     * @param MatcherInterface $matcher
     * @return $this
     */
    public function setMatcher(MatcherInterface $matcher)
    {
        $this->matcher = $matcher;
        return $this;
    }

    /**
     * @param Request|null $request
     * @return $this
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getReason(): ReasonInterface
    {
        return $this->reason;
    }

    /**
     * @return bool
     * @throws NoMatcherException
     * @throws Exception
     */
    public function matchAll(): bool
    {
        if ($this->matcher === null)
            throw new NoMatcherException('是否忘记调用setMatcher？');
        $routeNames = $this->routeContainer->names();
        foreach ($routeNames as $name) {
            $route = $this->routeContainer->get($name);
            if ($this->beforeMatch($route)) {
                if ($this->matcher->match(
                    $route,
                    $this->request->getPathInfo()
                )) {
                    $this->request->request->add($this->matcher->getAttributes());
                    $this->request->attributes->set('_controller', $route->getOption('_controller'));
                    return true;
                }
            } else {
                //非法请求
                return false;
            }
        }
        //未成功匹配
        $this->reason = new NoMatch($this->config, $this->logger);
        return false;
    }

    /**
     * @param RouteInterface $route
     * @return bool
     * @throws Exception
     */
    public function beforeMatch(RouteInterface $route): bool
    {
        //方法是否允许
        if ($route->getMethods() !== [] && !in_array($this->request->getMethod(), $route->getMethods())) {
            $this->reason = new MethodNotAllow($this->config, $this->logger);
            return false;
        }
        //host是否允许 未规定则视为全都允许
        if ($route->getHost() !== '' && $this->request->headers->get('host') !== $route->getHost()) {
            $this->reason = new HostNotAllow($this->config, $this->logger);
            return false;
        }
        return true;
    }
}