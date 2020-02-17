<?php

namespace Ipuppet\Jade\Component\Router\Test;

use Exception;
use Ipuppet\Jade\Component\Http\RequestFactory;
use Ipuppet\Jade\Component\Router\Exception\NoMatcherException;
use Ipuppet\Jade\Component\Router\Matcher\MatchByArray;
use Ipuppet\Jade\Component\Router\Route;
use Ipuppet\Jade\Component\Router\RouteContainer;
use Ipuppet\Jade\Component\Router\Router;
use PHPUnit\Framework\TestCase as TestCaseAlias;

class MatchByArrayTest extends TestCaseAlias
{
    /**
     * @param $name
     * @param $route
     * @param $request
     * @param $expected
     * @dataProvider routeProvider
     */
    public function testMatch($name, $route, $request, $expected)
    {
        $router = new Router();
        $router->setMatcher(new MatchByArray());
        $routeContainer = new RouteContainer();
        $routeContainer->set($name, $route);
        $router->setRouteContainer($routeContainer)->setRequest($request);
        try {
            $this->assertEquals($expected, $router->matchAll());
        } catch (NoMatcherException $e) {
        } catch (Exception $e) {
        }
    }

    public function routeProvider()
    {
        return [
            [
                'can_be_none__not_none',
                new Route('/hello/{name}', [], ['name' => '([a-zA-Z]*)']),
                RequestFactory::create([], [], [], [], [], ['REQUEST_URI' => '/hello/world']),
                true
            ],
            [
                'can_be_none__is_none',
                new Route('/hello/{name}', [], ['name' => '([a-zA-Z]*)']),
                RequestFactory::create([], [], [], [], [], ['REQUEST_URI' => '/hello/']),
                true
            ], [
                'can_not_be_none__not_none',
                new Route('/hello/{name}', [], ['name' => '([a-zA-Z]+)']),
                RequestFactory::create([], [], [], [], [], ['REQUEST_URI' => '/hello/world']),
                true
            ], [
                'can_not_be_none__is_none',
                new Route('/hello/{name}', [], ['name' => '([a-zA-Z]+)']),
                RequestFactory::create([], [], [], [], [], ['REQUEST_URI' => '/hello/']),
                false
            ], [
                'only_azAZ__azAZ',
                new Route('/hello/{name}', [], ['name' => '([a-zA-Z]+)']),
                RequestFactory::create([], [], [], [], [], ['REQUEST_URI' => '/hello/world']),
                true
            ], [
                'only_azAZ__num',
                new Route('/hello/{name}', [], ['name' => '([a-zA-Z]+)']),
                RequestFactory::create([], [], [], [], [], ['REQUEST_URI' => '/hello/2019']),
                false
            ], [
                'before_none',
                new Route('/hello/{name}', [], ['name' => '([a-zA-Z]+)']),
                RequestFactory::create([], [], [], [], [], ['REQUEST_URI' => '/world']),
                false
            ], [
                'after_has',
                new Route('/hello/{name}/end', [], ['name' => '([a-zA-Z]+)']),
                RequestFactory::create([], [], [], [], [], ['REQUEST_URI' => '/hello/world/end']),
                true
            ], [
                'after_none',
                new Route('/hello/{name}/end', [], ['name' => '([a-zA-Z]+)']),
                RequestFactory::create([], [], [], [], [], ['REQUEST_URI' => '/hello/world/']),
                false
            ],
        ];
    }
}
