<?php


namespace Zimings\Jade\Component\Router;


class RouteContainer
{
    /**
     * @var array
     */
    private $routes;

    public function add($routes)
    {
        if ($routes instanceof RouteContainer) {
            $routes = $routes->all();
        }
        foreach ($routes as $name => $route) {
            $this->set($name, $route);
        }
    }

    public function set($name, RouteInterface $route)
    {
        $this->routes[$name] = $route;
    }

    public function get($name): RouteInterface
    {
        return $this->routes[$name];
    }

    public function names()
    {
        return array_keys($this->routes);
    }

    public function all()
    {
        return $this->routes;
    }

    public static function createByArray(array $routes = []): self
    {
        $routeContainer = new self();
        foreach ($routes as $route) {
            $name = $route['name'];
            $path = $route['path'];
            $defaults = $route['defaults'] ?? [];
            $tokens = $route['tokens'] ?? [];
            $host = $route['host'] ?? '';
            $methods = [];
            if (isset($route['methods'])) {
                if (is_array($route['methods'])) {
                    foreach ($route['methods'] as $method) {
                        $methods[] = $method;
                    }
                } else {
                    $methods = [$route['methods']];
                }
            }
            $options['_controller'] = $route['_controller'];
            //转换为Route对象
            $route = new Route($path, $defaults, $tokens, $options, $host, $methods);
            $routeContainer->set($name, $route);
        }
        return $routeContainer;
    }
}