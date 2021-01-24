<?php


namespace Ipuppet\Jade\Component\Http;


use DateTime;
use Ipuppet\Jade\Foundation\Parameter\Parameter;
use Ipuppet\Jade\Foundation\Parameter\ParameterInterface;
use RuntimeException;

class Header extends Parameter implements ParameterInterface
{
    protected array $parameters = [];
    protected array $cacheControl = [];

    /**
     * @param array $headers An array of HTTP headers
     */
    public function __construct(array $headers = [])
    {
        parent::__construct();
        $this->add($headers);
    }

    /**
     * Returns the headers as a string.
     * @return string The headers
     */
    public function __toString(): string
    {
        if (!$headers = $this->toArray()) {
            return '';
        }
        ksort($headers);
        $max = max(array_map('strlen', array_keys($headers))) + 1;
        $content = '';
        foreach ($headers as $name => $values) {
            $name = implode('-', array_map('ucfirst', explode('-', $name)));
            foreach ($values as $value) {
                $content .= sprintf("%-{$max}s %s\r\n", $name . ':', $value);
            }
        }

        return $content;
    }

    protected function parseKey(string $key)
    {
        return str_replace('_', '-', strtolower($key));
    }

    /**
     * Adds new headers the current HTTP headers set.
     * @param array $headers An array of HTTP headers
     * @return $this
     */
    public function add(array $headers): self
    {
        foreach ($headers as $key => $values) {
            $this->set($key, $values);
        }
        return $this;
    }

    /**
     * Returns a header value by name.
     * @param string $key The header name
     * @param string|null $default The default value
     * @param bool $first Whether to return the first value or all header values
     * @return string|string[]|null The first header value or default value if $first is true, an array of values otherwise
     */
    public function get(string $key, $default = null, $first = true)
    {
        $key = $this->parseKey($key);
        $headers = $this->toArray();
        if (!$this->has($key)) {
            if (null === $default) {
                return $first ? null : [];
            }
            return $first ? $default : [$default];
        }
        if ($first) {
            if (!$headers[$key]) {
                return $default;
            }
            if (null === $headers[$key][0]) {
                return null;
            }
            return (string)$headers[$key][0];
        }
        return $headers[$key];
    }

    /**
     * Sets a header by name.
     * @param string $key The key
     * @param string|string[] $values The value or an array of values
     * @param bool $replace Whether to replace the actual value or not (true by default)
     * @return $this
     */
    public function set(string $key, $values, bool $replace = true): self
    {
        $key = $this->parseKey($key);
        if (is_array($values)) {
            $values = array_values($values);
            if (true === $replace || !isset($this->parameters[$key])) {
                $this->parameters[$key] = $values;
            } else {
                $this->parameters[$key] = array_merge($this->parameters[$key], $values);
            }
        } else {
            if (true === $replace || !isset($this->parameters[$key])) {
                $this->parameters[$key] = [$values];
            } else {
                $this->parameters[$key][] = $values;
            }
        }
        if ('cache-control' === $key) {
            $this->cacheControl = $this->parseCacheControl(implode(', ', $this->parameters[$key]));
        }
        return $this;
    }

    /**
     * Returns true if the HTTP header is defined.
     * @param string $key The HTTP header
     * @return bool true if the parameter exists, false otherwise
     */
    public function has(string $key): bool
    {
        return array_key_exists($this->parseKey($key), $this->toArray());
    }

    /**
     * Returns true if the given HTTP header contains the given value.
     * @param string $key The HTTP header name
     * @param string $value The HTTP value
     * @return bool true if the value is contained in the header, false otherwise
     */
    public function contains(string $key, string $value): bool
    {
        return in_array($value, $this->get($key, null, false));
    }

    /**
     * Removes a header.
     * @param string $key The HTTP header name
     * @return Header
     */
    public function remove(string $key): self
    {
        $key = $this->parseKey($key);
        unset($this->parameters[$key]);
        if ('cache-control' === $key) {
            $this->cacheControl = [];
        }
        return $this;
    }

    /**
     * Returns the HTTP header value converted to a date.
     * @param string $key The parameter key
     * @param DateTime|null $default The default value
     * @return DateTime|null The parsed DateTime or the default value if the header does not exist
     * @throws RuntimeException When the HTTP header is not parseable
     */
    public function getDate(string $key, DateTime $default = null): ?DateTime
    {
        if (null === $value = $this->get($key)) {
            return $default;
        }
        if (false === $date = DateTime::createFromFormat(DATE_RFC2822, $value)) {
            throw new RuntimeException(sprintf('The %s HTTP header is not parseable (%s).', $key, $value));
        }
        return $date;
    }

    /**
     * Adds a custom Cache-Control directive.
     * @param string $key The Cache-Control directive name
     * @param mixed $value The Cache-Control directive value
     */
    public function addCacheControlDirective(string $key, $value = true)
    {
        $this->cacheControl[$key] = $value;
        $this->set('Cache-Control', $this->getCacheControlHeader());
    }

    /**
     * Returns true if the Cache-Control directive is defined.
     * @param string $key The Cache-Control directive
     * @return bool true if the directive exists, false otherwise
     */
    public function hasCacheControlDirective(string $key): bool
    {
        return array_key_exists($key, $this->cacheControl);
    }

    /**
     * Returns a Cache-Control directive value by name.
     * @param string $key The directive name
     * @return mixed|null The directive value if defined, null otherwise
     */
    public function getCacheControlDirective(string $key)
    {
        return array_key_exists($key, $this->cacheControl) ? $this->cacheControl[$key] : null;
    }

    /**
     * Removes a Cache-Control directive.
     * @param string $key The Cache-Control directive
     */
    public function removeCacheControlDirective(string $key)
    {
        unset($this->cacheControl[$key]);
        $this->set('Cache-Control', $this->getCacheControlHeader());
    }

    protected function getCacheControlHeader(): string
    {
        $parts = [];
        ksort($this->cacheControl);
        foreach ($this->cacheControl as $key => $value) {
            if (true === $value) {
                $parts[] = $key;
            } else {
                if (preg_match('#[^a-zA-Z0-9._-]#', $value)) {
                    $value = '"' . $value . '"';
                }
                $parts[] = "$key=$value";
            }
        }
        return implode(', ', $parts);
    }

    /**
     * Parses a Cache-Control HTTP header.
     * @param string $header The value of the Cache-Control HTTP header
     * @return array An array representing the attribute values
     */
    protected function parseCacheControl(string $header): array
    {
        $cacheControl = [];
        preg_match_all('#([a-zA-Z][a-zA-Z_-]*)\s*(?:=(?:"([^"]*)"|([^ \t",;]*)))?#', $header, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $cacheControl[strtolower($match[1])] = isset($match[3]) ? $match[3] : (isset($match[2]) ? $match[2] : true);
        }
        return $cacheControl;
    }
}