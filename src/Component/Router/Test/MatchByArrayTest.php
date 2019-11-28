<?php

namespace Jade\Component\Router\Test;

use Jade\Component\Http\RequestFactory;
use Jade\Component\Kernel\ConfigLoader\Exception\ConfigLoaderException;
use Jade\Component\Router\Exception\NoMatcherException;
use Jade\Component\Router\Matcher\MatchByArray;
use Jade\Component\Router\Route;
use Jade\Component\Router\RouteContainer;
use Jade\Component\Router\Router;
use Jade\Foundation\Path\Exception\PathException;
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
        $routeContainer->addRoute($name, $route);
        $router->setRouteContainer($routeContainer)->setRequest($request);
        try {
            $this->assertEquals($expected, $router->matchAll());
        } catch (ConfigLoaderException $e) {
        } catch (NoMatcherException $e) {
        } catch (PathException $e) {
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
