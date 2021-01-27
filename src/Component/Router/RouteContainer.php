<?php


namespace Ipuppet\Jade\Component\Router;


class RouteContainer
{
    /**
     * @var array
     */
    private array $routes;

    /**
     * 批量添加
     * @param $routes
     */
    public function add($routes)
    {
        if ($routes instanceof RouteContainer) {
            $routes = $routes->all();
        }
        foreach ($routes as $name => $route) {
            $this->set($name, $route);
        }
    }

    /**
     * 单个设置
     * @param $name
     * @param RouteInterface $route
     */
    public function set($name, RouteInterface $route)
    {
        $this->routes[$name] = $route;
    }

    public function get($name): RouteInterface
    {
        return $this->routes[$name];
    }

    public function names(): array
    {
        return array_keys($this->routes);
    }

    public function all(): array
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
            $options['controller'] = $route['controller'];
            //转换为Route对象
            $route = new Route($path, $defaults, $tokens, $options, $methods);
            $routeContainer->set($name, $route);
        }
        return $routeContainer;
    }
}