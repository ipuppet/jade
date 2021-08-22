<?php


namespace Ipuppet\Jade\Component\Router\Matcher;


use Ipuppet\Jade\Component\Router\RouteInterface;
use Psr\Log\LoggerInterface;

interface MatcherInterface
{
    /**
     * @param LoggerInterface $logger
     * @return MatcherInterface
     */
    public function setLogger(LoggerInterface $logger): MatcherInterface;

    /**
     * 匹配一次
     * @param RouteInterface $route
     * @param string $requestPath 请求的path
     * @return bool
     */
    public function match(RouteInterface $route, string $requestPath): bool;

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
