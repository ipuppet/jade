<?php


namespace Ipuppet\Jade\Component\Router\Matcher;


use Ipuppet\Jade\Component\Router\RouteInterface;
use Psr\Log\LoggerInterface;

abstract class Matcher implements MatcherInterface
{
    /**
     * @var array
     */
    protected array $attributes;

    /**
     * 严格模式，默认关闭
     * 开启后路由结尾不能有'/'
     * @var bool
     */
    private bool $strictMode;
    private ?LoggerInterface $logger = null;

    public function __construct(bool $strictMode = false)
    {
        $this->strictMode = $strictMode;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger): MatcherInterface
    {
        $this->logger = $logger;
        return $this;
    }

    public function strictMode(): MatcherInterface
    {
        $this->strictMode = true;
        return $this;
    }

    public function isStrictMode(): bool
    {
        return $this->strictMode;
    }

    /**
     * @param RouteInterface $route
     * @param string $requestPath
     * @return bool
     */
    abstract public function match(RouteInterface $route, string $requestPath): bool;

    /**
     * 返回占位符匹配结果
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @param RouteInterface|null $route
     */
    protected function setAttributes(array $attributes, RouteInterface $route = null)
    {
        if ($route !== null) {
            array_walk($attributes, function (&$attribute, $name) use (&$route) {
                if (!is_numeric($attribute) && empty($attribute)) {
                    if ($route->hasDefault($name)) {
                        $attribute = $route->getDefault($name);
                    } else {
                        $attribute = null;
                    }
                }
            });
        }
        $this->attributes = $attributes;
    }

    public function defaultToken(): string
    {
        return '([_0-9a-zA-Z\x{4e00}-\x{9fa5}]*)';
    }
}
