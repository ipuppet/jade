<?php


namespace Jade\Component\Router\Matcher;


use Jade\Component\Router\RouteInterface;

class MatchByRegexPath extends Matcher implements MatcherInterface
{
    /**
     * 将整个routePath替换为一个正则表达式
     * @param RouteInterface $route
     * @param string $requestPath
     * @return bool
     */
    public function match(RouteInterface $route, $requestPath): bool
    {
        $tokenPath = $route->getPath();
        foreach ($route->getPlaceholders() as $placeholder) {
            if ($route->hasToken($placeholder)) {
                $token = $route->getToken($placeholder);
            } else {
                $token = $this->defaultToken();
            }
            $tokenPath = str_replace('{' . $placeholder . '}', $token, $tokenPath);
        }
        $tokenPath = '/^' . str_replace('/', '\\/', $tokenPath);
        if ($this->isStrictMode()) {
            $tokenPath .= '$/u';
        } else {
            $tokenPath .= '\/?$/u';
        }
        if (preg_match($tokenPath, urldecode($requestPath), $attributes)) {
            unset($attributes[0]);
            $attributes = array_combine(array_keys($route->getPlaceholders()), $attributes);
            $this->setAttributes($attributes);
            return true;
        }
        return false;
    }
}