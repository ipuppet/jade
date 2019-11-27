<?php


namespace Jade\Component\Router\Test;


use Jade\Component\Http\RequestFactory;
use Jade\Component\Kernel\ConfigLoader\Exception\ConfigLoaderException;
use Jade\Component\Router\Exception\NoMatcherException;
use Jade\Component\Router\Matcher\MatchByArray;
use Jade\Component\Router\Matcher\MatchByRegexPath;
use Jade\Component\Router\Route;
use Jade\Component\Router\RouteContainer;
use Jade\Component\Router\Router;
use Jade\Foundation\Path\Exception\PathException;

include '../../../../vendor/autoload.php';

class Test
{
    const TEST_TIMES = 2000;

    public function test()
    {
        $routes = [];
        $test = [];
        echo 'start create path...' . PHP_EOL;
        $startTime = microtime(true);
        for ($i = 0; $i < self::TEST_TIMES; $i++) {
            $route['name'] = $this->getRandStr(6);
            $placeholderNum = mt_rand(1, 6);
            $placeholder = [];
            for ($j = 0; $j < $placeholderNum; $j++) {
                $pathStr = $this->getRandStr(5);
                if (mt_rand(0, 1000) % 3 === 0) {
                    $pathStr = '{' . $pathStr . '}';
                }
                $placeholder[] = $pathStr;
            }
            if (mt_rand(0, 1000) % 3 === 0) {
                $arr = $placeholder;
                foreach (array_keys($arr) as $key) {
                    $arr[$key] = str_replace('{', '', $arr[$key]);
                    $arr[$key] = str_replace('}', '', $arr[$key]);
                }
                $test[] = '/' . implode('/', $arr) . '/';
            }
            $route['path'] = '/' . implode('/', $placeholder) . '/';
            $routes[] = $route;
        }
        $routeContainer = $this->createRouteContainerByArray($routes);
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        echo 'path success created, use: ' . $time . PHP_EOL;

        echo 'start match...' . PHP_EOL;
        $server = $_SERVER;
        $success = [];
        $error = [];
        $startTime = microtime(true);
        foreach ($test as $path) {
            $server['REQUEST_URI'] = $path;
            $request = RequestFactory::create(
                [], [], [], [], [], $server
            );
            $router = new Router($request, $routeContainer);
            //$router->setMatcher(new MatchByArray());
            $router->setMatcher(new MatchByRegexPath());
            try {
                if ($router->matchAll()) {
                    $success[] = "success: \n        {$path}\n        {$request->getPathInfo()}";
                } else {
                    $error[] = "error: \n        {$path}\n        {$request->getPathInfo()}";
                }
            } catch (ConfigLoaderException $e) {
            } catch (PathException $e) {
            } catch (NoMatcherException $e) {
            }
        }
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        print_r($success);
        echo 'match finished, use: ' . $time . PHP_EOL;
    }

    public function getRandStr($length)
    {
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len = strlen($str) - 1;
        $randstr = '';
        for ($i = 0; $i < $length; $i++) {
            $num = mt_rand(0, $len);
            $randstr .= $str[$num];
        }
        return $randstr;
    }

    public function createRouteContainerByArray(array $routes = []): RouteContainer
    {
        $routeContainer = new RouteContainer();
        foreach ($routes as $route) {
            $name = $route['name'];
            $path = $route['path'];
            $defaults = $route['defaults'] ?? [];
            $requirements = $route['requirements'] ?? [];
            $host = $route['host'] ?? '';
            if (isset($route['methods'])) {
                if (is_array($route['methods'])) {
                    foreach ($route['methods'] as $method) {
                        $methods = $method;
                    }
                } else {
                    $methods = [$route['methods']];
                }
            } else {
                $methods = [];
            }
            //转换为Route对象
            $route = new Route($path, $defaults, $requirements, [], $host, $methods);
            $routeContainer->addRoute($name, $route);
        }
        return $routeContainer;
    }
}

$test = new Test();
$test->test();