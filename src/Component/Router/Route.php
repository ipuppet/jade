<?php


namespace Ipuppet\Jade\Component\Router;


use InvalidArgumentException;

class Route implements RouteInterface
{
    /**
     * @var string
     */
    private string $path = '/';

    /**
     * @var array
     */
    private array $methods;

    /**
     * 占位符的默认值
     * @var array
     */
    private array $defaults;

    /**
     * 占位符token
     * @var array
     */
    private array $tokens;

    /**
     * 占位符匹配到的参数
     * @var array
     */
    private array $parameters = [];

    /**
     * 用于存放例如控制器之类的信息
     * @var array
     */
    private array $options;

    /**
     * @var array
     */
    private array $placeholders = [];

    public function __construct($path, array $defaults = [], array $tokens = [], array $options = [], $methods = [])
    {
        $this->setPath($path);
        $this->setDefaults($defaults);
        $this->setTokens($tokens);
        $this->setOptions($options);
        $this->setMethods($methods);
    }

    /**
     * Returns the uppercased HTTP methods this route is restricted to.
     * So an empty array means that any method is allowed.
     * @return array The methods
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Sets the HTTP methods (e.g. 'POST') this route is restricted to.
     * So an empty array means that any method is allowed.
     * @param string|array $methods The method or an array of methods
     * @return RouteInterface
     */
    public function setMethods($methods): RouteInterface
    {
        $this->methods = array_map('strtoupper', (array)$methods);
        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $Parameters): RouteInterface
    {
        return $this->addParameters($Parameters);
    }

    public function addParameters(array $parameters): RouteInterface
    {
        foreach ($parameters as $name => $parameter) {
            $this->parameters[$name] = $parameter;
        }
        return $this;
    }

    public function setParameter($name, $value): RouteInterface
    {
        $this->parameters[$name] = $value;
        return $this;
    }

    public function getParameter($name)
    {
        return $this->parameters[$name] ?? null;
    }

    public function hasParameter($name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): RouteInterface
    {
        return $this->addOptions($options);
    }

    public function addOptions(array $options): RouteInterface
    {
        foreach ($options as $name => $option) {
            $this->options[$name] = $option;
        }
        return $this;
    }

    public function setOption($name, $value): RouteInterface
    {
        $this->options[$name] = $value;
        return $this;
    }

    public function getOption($name)
    {
        return $this->options[$name] ?? null;
    }

    public function hasOption($name): bool
    {
        return array_key_exists($name, $this->options);
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function setDefaults(array $defaults): RouteInterface
    {
        $this->defaults = [];
        return $this->addDefaults($defaults);
    }

    public function addDefaults(array $defaults): RouteInterface
    {
        foreach ($defaults as $name => $default) {
            $this->defaults[$name] = $default;
        }
        return $this;
    }

    public function getDefault($name)
    {
        return $this->defaults[$name] ?? null;
    }

    public function hasDefault($name): bool
    {
        return array_key_exists($name, $this->defaults);
    }

    public function setDefault($name, $default): RouteInterface
    {
        $this->defaults[$name] = $default;
        return $this;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function setTokens(array $tokens): RouteInterface
    {
        $this->tokens = [];

        return $this->addTokens($tokens);
    }

    public function addTokens(array $tokens): RouteInterface
    {
        foreach ($tokens as $key => $regex) {
            $this->tokens[$key] = $this->sanitizeToken($key, $regex);
        }
        return $this;
    }

    public function getToken($key)
    {
        return $this->tokens[$key] ?? null;
    }

    public function setToken($key, $regex): RouteInterface
    {
        $this->tokens[$key] = $this->sanitizeToken($key, $regex);
        return $this;
    }

    private function sanitizeToken($key, $regex): string
    {
        if (!is_string($regex)) {
            throw new InvalidArgumentException(sprintf('Routing token for "%s" must be a string.', $key));
        }
        if ('' === $regex) {
            throw new InvalidArgumentException(sprintf('Routing token for "%s" cannot be empty.', $key));
        }
        if ('^' === $regex[0]) {
            $regex = substr($regex, 1); // returns false for a single character
        }
        if (str_ends_with($regex, '$')) {
            $regex = substr($regex, 0, -1);
        }
        if ('(' !== $regex[0]) {
            $regex = '(' . $regex;
        }
        if (!str_ends_with($regex, ')')) {
            $regex .= ')';
        }
        return $regex;
    }

    public function hasToken($key): bool
    {
        return array_key_exists($key, $this->tokens);
    }

    /**
     * @return array
     */
    public function getPlaceholders(): array
    {
        if (empty($this->placeholders)) {
            if (strpos($this->getPath(), '{')) {
                //匹配花括号中的内容
                preg_match_all('/(?<={)[^}]+/', $this->getPath(), $this->placeholders);
                $this->placeholders = $this->placeholders[0];
            } else {
                $this->placeholders = [];
            }
        }
        return $this->placeholders;
    }

    /**
     * Returns the pattern for the path.
     * @return string The path pattern
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Sets the pattern for the path.
     * @param string $pattern The path pattern
     * @return RouteInterface
     */
    public function setPath(string $pattern): RouteInterface
    {
        // A pattern must start with a slash and must not have multiple slashes at the beginning because the
        // generated path for this route would be confused with a network path, e.g. '//domain.com/path'.
        $this->path = '/' . ltrim(trim($pattern), '/');
        return $this;
    }
}
