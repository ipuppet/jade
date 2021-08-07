<?php


namespace Ipuppet\Jade\Component\Router;


use Exception;
use Ipuppet\Jade\Component\Http\Request;
use Ipuppet\Jade\Component\Kernel\Config\Config;
use Ipuppet\Jade\Component\Router\Exception\NoMatcherException;
use Ipuppet\Jade\Component\Router\Matcher\MatcherInterface;
use Ipuppet\Jade\Component\Router\Reason\MethodNotAllow;
use Ipuppet\Jade\Component\Router\Reason\NoMatch;
use Ipuppet\Jade\Component\Router\Reason\ReasonInterface;
use Psr\Log\LoggerInterface;

class Router
{
    /**
     * @var ?Request
     */
    private ?Request $request;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ?RouteContainer
     */
    private ?RouteContainer $routeContainer;

    /**
     * @var ReasonInterface
     */
    private ReasonInterface $reason;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var MatcherInterface
     */
    private MatcherInterface $matcher;

    public function __construct(Request $request = null, RouteContainer $routeContainer = null)
    {
        $this->request = $request;
        $this->routeContainer = $routeContainer;
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param RouteContainer $routeContainer
     * @return $this
     */
    public function setRouteContainer(RouteContainer $routeContainer): self
    {
        $this->routeContainer = $routeContainer;
        return $this;
    }

    /**
     * @param MatcherInterface $matcher
     * @return $this
     */
    public function setMatcher(MatcherInterface $matcher): self
    {
        $this->matcher = $matcher;
        return $this;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request|null $request
     * @return $this
     */
    public function setRequest(Request $request = null): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * 是否将错误写入日志
     * @param bool $logAccessError
     * @return ReasonInterface
     */
    public function getReason(bool $logAccessError = false): ReasonInterface
    {
        if ($logAccessError)
            $this->logger->error("Access error '{$this->request->getPathInfo()}' {$this->reason->getDescription()}");
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
            if ($this->matcher->match(
                $route,
                $this->request->getPathInfo()
            )) {
                if ($this->afterMatch($route)) {
                    $this->request->request->add($this->matcher->getAttributes());
                    $this->request->attributes->set('controller', $route->getOption('controller'));
                    return true;
                }
            }
        }
        // 未成功匹配
        $this->reason = new NoMatch($this->config, $this->logger);
        return false;
    }

    /**
     * @param RouteInterface $route
     * @return bool
     * @throws Exception
     */
    public function afterMatch(RouteInterface $route): bool
    {
        // 方法是否允许，未规定则视为全都允许
        if (
            $route->getMethods() !== [] &&
            !in_array($this->request->getMethod(), $route->getMethods()) &&
            $this->request->getMethod() !== 'OPTIONS' // 所有OPTIONS请求都跳过检查
        ) {
            $this->reason = new MethodNotAllow($this->config, $this->logger);
            return false;
        }
        return true;
    }
}