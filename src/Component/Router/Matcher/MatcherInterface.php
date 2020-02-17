<?php


namespace Ipuppet\Jade\Component\Router\Matcher;


use Ipuppet\Jade\Component\Router\RouteInterface;

interface MatcherInterface
{
    /**
     * 匹配一次
     * @param $routePath string 定义的path规则
     * @param $requestPath string 请求的path
     * @param $tokens array 所有占位符都会有token，未设置token的占位符默认token为：'([0-9a-zA-Z_\x{4e00}-\x{9fa5}]+)'
     * @return bool
     */
    public function match(RouteInterface $route, $requestPath): bool;

    /**
     * 返回占位符匹配结果
     * @return array
     */
    public function getAttributes(): array;

    /**
     * 是否是严格模式
     * @return bool
     */
    public function isStrictMode(): bool;

    /**
     * 开启严格模式
     * @return MatcherInterface
     */
    public function strictMode(): MatcherInterface;
}