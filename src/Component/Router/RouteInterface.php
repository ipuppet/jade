<?php


namespace Ipuppet\Jade\Component\Router;


interface RouteInterface
{
    public function getPath();

    /**
     * Sets the pattern for the path.
     * @param string $pattern The path pattern
     * @return $this
     */
    public function setPath(string $pattern): self;

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
     * @return $this
     */
    public function setMethods($methods): self;

    /**
     * @return array
     */
    public function getParameters(): array;

    /**
     * @param array $Parameters
     * @return $this
     */
    public function setParameters(array $Parameters): self;

    /**
     * @param array $parameters
     * @return $this
     */
    public function addParameters(array $parameters): self;

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setParameter($name, $value): self;

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
     * @return $this
     */
    public function setOptions(array $options): self;

    /**
     * @param array $options
     * @return $this
     */
    public function addOptions(array $options): self;

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setOption($name, $value): self;

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
     * @return $this
     */
    public function setDefaults(array $defaults): self;

    /**
     * @param array $defaults
     * @return $this
     */
    public function addDefaults(array $defaults): self;

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
     * @return $this
     */
    public function setDefault($name, $default): self;

    /**
     * @return array
     */
    public function getTokens(): array;

    /**
     * @param array $tokens
     * @return $this
     */
    public function setTokens(array $tokens): self;

    /**
     * @param array $tokens
     * @return $this
     */
    public function addTokens(array $tokens): self;

    /**
     * @param $key
     * @return mixed
     */
    public function getToken($key);

    /**
     * @param $key
     * @param $regex
     * @return $this
     */
    public function setToken($key, $regex): self;

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