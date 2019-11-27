<?php


namespace Jade\Component\Router;


use InvalidArgumentException;

class Route implements RouteInterface
{
    /**
     * @var string
     */
    private $path = '/';

    /**
     * @var string
     */
    private $host;

    /**
     * @var array
     */
    private $methods;

    /**
     * 占位符的默认值
     * @var array
     */
    private $defaults;

    /**
     * 占位符token
     * @var array
     */
    private $tokens;

    /**
     * 占位符匹配到的参数
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $placeholder;

    public function __construct($path, array $defaults = [], array $tokens = [], array $options = [], $host = '', $methods = [])
    {
        $this->setPath($path);
        $this->setDefaults($defaults);
        $this->setTokens($tokens);
        $this->setOptions($options);
        $this->setHost($host);
        $this->setMethods($methods);
    }

    /**
     * Returns the pattern for the path.
     *
     * @return string The path pattern
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the pattern for the path.
     *
     * @param string $pattern The path pattern
     *
     * @return $this
     */
    public function setPath($pattern)
    {
        // A pattern must start with a slash and must not have multiple slashes at the beginning because the
        // generated path for this route would be confused with a network path, e.g. '//domain.com/path'.
        $this->path = '/' . ltrim(trim($pattern), '/');

        return $this;
    }

    /**
     * Returns the pattern for the host.
     *
     * @return string The host pattern
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets the pattern for the host.
     *
     * @param string $pattern The host pattern
     *
     * @return $this
     */
    public function setHost($pattern)
    {
        $this->host = (string)$pattern;

        return $this;
    }

    /**
     * Returns the uppercased HTTP methods this route is restricted to.
     * So an empty array means that any method is allowed.
     *
     * @return array The methods
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Sets the HTTP methods (e.g. 'POST') this route is restricted to.
     * So an empty array means that any method is allowed.
     *
     * @param string|array $methods The method or an array of methods
     *
     * @return $this
     */
    public function setMethods($methods)
    {
        $this->methods = array_map('strtoupper', (array)$methods);

        return $this;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters(array $Parameters)
    {
        return $this->addParameters($Parameters);
    }

    public function addParameters(array $parameters)
    {
        foreach ($parameters as $name => $parameter) {
            $this->parameters[$name] = $parameter;
        }

        return $this;
    }

    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function getParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        return $this->addOptions($options);
    }

    public function addOptions(array $options)
    {
        foreach ($options as $name => $option) {
            $this->options[$name] = $option;
        }

        return $this;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    public function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    public function getDefaults()
    {
        return $this->defaults;
    }

    public function setDefaults(array $defaults)
    {
        $this->defaults = [];

        return $this->addDefaults($defaults);
    }

    public function addDefaults(array $defaults)
    {
        foreach ($defaults as $name => $default) {
            $this->defaults[$name] = $default;
        }

        return $this;
    }

    public function getDefault($name)
    {
        return isset($this->defaults[$name]) ? $this->defaults[$name] : null;
    }

    public function hasDefault($name)
    {
        return array_key_exists($name, $this->defaults);
    }

    public function setDefault($name, $default)
    {
        $this->defaults[$name] = $default;

        return $this;
    }

    public function getTokens()
    {
        return $this->tokens;
    }

    public function setTokens(array $tokens)
    {
        $this->tokens = [];

        return $this->addTokens($tokens);
    }

    public function addTokens(array $tokens)
    {
        foreach ($tokens as $key => $regex) {
            $this->tokens[$key] = $this->sanitizeToken($key, $regex);
        }

        return $this;
    }

    public function getToken($key)
    {
        return isset($this->tokens[$key]) ? $this->tokens[$key] : null;
    }

    public function setToken($key, $regex)
    {
        $this->tokens[$key] = $this->sanitizeToken($key, $regex);

        return $this;
    }

    public function hasToken($key)
    {
        return array_key_exists($key, $this->tokens);
    }

    private function sanitizeToken($key, $regex)
    {
        if (!is_string($regex)) {
            throw new InvalidArgumentException(sprintf('Routing token for "%s" must be a string.', $key));
        }

        if ('' !== $regex && '^' === $regex[0]) {
            $regex = (string)substr($regex, 1); // returns false for a single character
        }

        if ('$' === substr($regex, -1)) {
            $regex = substr($regex, 0, -1);
        }

        if ('' === $regex) {
            throw new InvalidArgumentException(sprintf('Routing token for "%s" cannot be empty.', $key));
        }

        return $regex;
    }

    /**
     * @return array
     */
    public function getPlaceholders()
    {
        if ($this->placeholder === null) {
            if (strpos($this->getPath(), '{')) {
                //匹配花括号中的内容
                preg_match_all('/(?<={)[^}]+/', $this->getPath(), $this->placeholder);
                $this->placeholder = $this->placeholder[0];
            } else {
                $this->placeholder = [];
            }
        }
        return $this->placeholder;
    }
}