<?php


namespace Ipuppet\Jade\Component\Router;


interface RouteInterface
{
    public function getPath();

    /**
     * Sets the pattern for the path.
     * @param string $pattern The path pattern
     * @return RouteInterface
     */
    public function setPath(string $pattern): RouteInterface;

    /**
     * Returns the uppercased HTTP methods this route is restricted to.
     * So an empty array means that any method is allowed.
     * @return array The methods
     */
    public function getMethods(): array;

    /**
     * Sets the HTTP methods (e.g. 'POST') this route is restricted to.
     * So an empty array means that any method is allowed.
     * @param string|array $methods The method or an array of methods
     * @return RouteInterface
     */
    public function setMethods($methods): RouteInterface;

    /**
     * @return array
     */
    public function getParameters(): array;

    /**
     * @param array $Parameters
     * @return RouteInterface
     */
    public function setParameters(array $Parameters): RouteInterface;

    /**
     * @param array $parameters
     * @return RouteInterface
     */
    public function addParameters(array $parameters): RouteInterface;

    /**
     * @param $name
     * @param $value
     * @return RouteInterface
     */
    public function setParameter($name, $value): RouteInterface;

    /**
     * @param $name
     * @return mixed
     */
    public function getParameter($name);

    /**
     * @param $name
     * @return bool
     */
    public function hasParameter($name): bool;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param array $options
     * @return RouteInterface
     */
    public function setOptions(array $options): RouteInterface;

    /**
     * @param array $options
     * @return RouteInterface
     */
    public function addOptions(array $options): RouteInterface;

    /**
     * @param $name
     * @param $value
     * @return RouteInterface
     */
    public function setOption($name, $value): RouteInterface;

    /**
     * @param $name
     * @return mixed
     */
    public function getOption($name);

    /**
     * @param $name
     * @return bool
     */
    public function hasOption($name): bool;

    /**
     * @return array
     */
    public function getDefaults(): array;

    /**
     * @param array $defaults
     * @return RouteInterface
     */
    public function setDefaults(array $defaults): RouteInterface;

    /**
     * @param array $defaults
     * @return RouteInterface
     */
    public function addDefaults(array $defaults): RouteInterface;

    /**
     * @param $name
     * @return mixed
     */
    public function getDefault($name);

    /**
     * @param $name
     * @return bool
     */
    public function hasDefault($name): bool;

    /**
     * @param $name
     * @param $default
     * @return RouteInterface
     */
    public function setDefault($name, $default): RouteInterface;

    /**
     * @return array
     */
    public function getTokens(): array;

    /**
     * @param array $tokens
     * @return RouteInterface
     */
    public function setTokens(array $tokens): RouteInterface;

    /**
     * @param array $tokens
     * @return RouteInterface
     */
    public function addTokens(array $tokens): RouteInterface;

    /**
     * @param $key
     * @return mixed
     */
    public function getToken($key);

    /**
     * @param $key
     * @param $regex
     * @return RouteInterface
     */
    public function setToken($key, $regex): RouteInterface;

    /**
     * @param $key
     * @return bool
     */
    public function hasToken($key): bool;

    /**
     * @return array
     */
    public function getPlaceholders(): array;
}
