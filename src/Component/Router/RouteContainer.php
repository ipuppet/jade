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
                    $methods = $route['methods'];
                } else {
                    // 当只有一种请求方法受到支持时，您应该使用 'method' 而不是 'methods'
                    $methods = [$route['methods']];
                }
            } else if (isset($route['method'])) {
                $methods = [$route['method']];
            }
            $options['controller'] = $route['controller'];
            //转换为Route对象
            $route = new Route($path, $defaults, $tokens, $options, $methods);
            $routeContainer->set($name, $route);
        }
        return $routeContainer;
    }
}