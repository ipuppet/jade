<?php


namespace Ipuppet\Jade\Component\Router\Matcher;


use Ipuppet\Jade\Component\Router\RouteInterface;

class MatchByArray extends Matcher implements MatcherInterface
{
    /**
     * 通过转换成数组进行匹配
     * @param RouteInterface $route
     * @param string $requestPath
     * @return bool
     */
    public function match(RouteInterface $route, string $requestPath): bool
    {
        $attributes = [];
        $requestArray = explode('/', urldecode($requestPath));
        $routeArray = explode('/', $route->getPath());
        $len = count($routeArray);
        //数组长度相差>2匹配失败
        if (count($requestArray) - $len > 2) {
            return false;
        } else if (count($requestArray) - $len === 1) {
            //判断是否开启严格模式
            if (!$this->isStrictMode()) {
                if ($requestArray[count($requestArray) - 1] !== '') {
                    return false;
                }
            } else {
                return false;
            }
        }
        if ($len > count($requestArray))
            return false;
        //匹配占位符
        for ($i = 0; $i < $len; $i++) {
            if (mb_strpos($routeArray[$i], '}')) {
                $placeholder = mb_substr($routeArray[$i], 1, mb_strlen($routeArray[$i]) - 2);
                if ($route->hasToken($placeholder)) {
                    $token = $route->getToken($placeholder);
                } else {
                    $token = $this->defaultToken();
                }
                if (preg_match('/' . $token . '/u', $requestArray[$i])) {
                    $attributes[$placeholder] = $requestArray[$i];
                } else {
                    return false;
                }
            } else if ($routeArray[$i] !== $requestArray[$i]) {
                return false;
            }
        }
        $this->setAttributes($attributes, $route);
        return true;
    }
}