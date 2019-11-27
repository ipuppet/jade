<?php


namespace Jade\Component\Router;


interface RouteInterface
{
    public function getPath();

    /**
     * Sets the pattern for the path.
     * @param string $pattern The path pattern
     * @return $this
     */
    public function setPath($pattern);

    /**
     * Returns the pattern for the host.
     * @return string The host pattern
     */
    public function getHost();

    /**
     * Sets the pattern for the host.
     * @param string $pattern The host pattern
     * @return $this
     */
    public function setHost($pattern);

    /**
     * Returns the uppercased HTTP methods this route is restricted to.
     * So an empty array means that any method is allowed.
     * @return array The methods
     */
    public function getMethods();

    /**
     * Sets the HTTP methods (e.g. 'POST') this route is restricted to.
     * So an empty array means that any method is allowed.
     * @param string|array $methods The method or an array of methods
     * @return $this
     */
    public function setMethods($methods);

    /**
     * @return mixed
     */
    public function getParameters();

    /**
     * @param array $Parameters
     * @return mixed
     */
    public function setParameters(array $Parameters);

    /**
     * @param array $parameters
     * @return mixed
     */
    public function addParameters(array $parameters);

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function setParameter($name, $value);

    /**
     * @param $name
     * @return mixed
     */
    public function getParameter($name);

    /**
     * @param $name
     * @return mixed
     */
    public function hasParameter($name);

    /**
     * @return mixed
     */
    public function getOptions();

    /**
     * @param array $options
     * @return mixed
     */
    public function setOptions(array $options);

    /**
     * @param array $options
     * @return mixed
     */
    public function addOptions(array $options);

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function setOption($name, $value);

    /**
     * @param $name
     * @return mixed
     */
    public function getOption($name);

    /**
     * @param $name
     * @return mixed
     */
    public function hasOption($name);

    /**
     * @return mixed
     */
    public function getDefaults();

    /**
     * @param array $defaults
     * @return mixed
     */
    public function setDefaults(array $defaults);

    /**
     * @param array $defaults
     * @return mixed
     */
    public function addDefaults(array $defaults);

    /**
     * @param $name
     * @return mixed
     */
    public function getDefault($name);

    /**
     * @param $name
     * @return mixed
     */
    public function hasDefault($name);

    /**
     * @param $name
     * @param $default
     * @return mixed
     */
    public function setDefault($name, $default);

    /**
     * @return mixed
     */
    public function getTokens();

    /**
     * @param array $tokens
     * @return mixed
     */
    public function setTokens(array $tokens);

    /**
     * @param array $tokens
     * @return mixed
     */
    public function addTokens(array $tokens);

    /**
     * @param $key
     * @return mixed
     */
    public function getToken($key);

    /**
     * @param $key
     * @param $regex
     * @return mixed
     */
    public function setToken($key, $regex);

    /**
     * @param $key
     * @return mixed
     */
    public function hasToken($key);

    /**
     * @return mixed
     */
    public function getPlaceholders();
}