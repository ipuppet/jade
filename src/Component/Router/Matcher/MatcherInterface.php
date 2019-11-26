<?php


namespace Jade\Component\Router\Matcher;


use Jade\Component\Http\Request;
use Jade\Component\Router\Route;

interface MatcherInterface
{
    /**
     * 匹配一次
     * @param Route $route
     * @return bool
     */
    public function match(Route $route): bool;

    public function getRequest(): Request;

    public function setRequest(Request $request);
}