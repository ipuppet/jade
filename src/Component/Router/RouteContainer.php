<?php


namespace Zimings\Jade\Component\Router;


class RouteContainer
{
    /**
     * @var array
     */
    private $routes;

    public function __construct($routes = [])
    {
        $this->setRoutes($routes);
    }

    /**
     * @param mixed $routes
     */
    public function setRoutes($routes)
    {
        if ($routes !== [] || $routes !== null)
            $this->addRoutes($routes);
    }

    public function addRoutes($routes)
    {
        if ($routes instanceof RouteContainer) {
            $routes = $routes->getRoutes();
        }
        foreach ($routes as $name => $route) {
            $this->addRoute($name, $route);
        }
    }

    public function addRoute($name, RouteInterface $route)
    {
        $this->routes[$name] = $route;
    }

    public function getRoute($name): RouteInterface
    {
        return $this->routes[$name];
    }

    public function getNames()
    {
        return array_keys($this->routes);
    }

    public function getRoutes()
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
            $routeContainer->addRoute($name, $route);
        }
        return $routeContainer;
    }
}