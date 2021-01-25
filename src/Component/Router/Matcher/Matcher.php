<?php


namespace Ipuppet\Jade\Component\Router\Matcher;


use Ipuppet\Jade\Component\Router\RouteInterface;

abstract class Matcher implements MatcherInterface
{
    /**
     * 严格模式，默认关闭
     * 开启后路由结尾不能有'/'
     * @var bool
     */
    private bool $strictMode;

    /**
     * @var array
     */
    protected array $attributes;

    public function __construct(bool $strictMode = false)
    {
        $this->strictMode = $strictMode;
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
        return array_filter($this->attributes);
    }

    /**
     * @param array $attributes
     * @param RouteInterface|null $route
     */
    protected function setAttributes(array $attributes, RouteInterface $route = null)
    {
        if ($route !== null) {
            array_walk($attributes, function (&$attribute, $name) use (&$route) {
                if (empty($attribute)) {
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