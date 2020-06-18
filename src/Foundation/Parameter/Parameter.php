<?php


namespace Ipuppet\Jade\Foundation\Parameter;


use ArrayIterator;

class Parameter implements ParameterInterface
{
    /**
     * @var array
     */
    protected $parameters;

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    public function keys(): array
    {
        return array_keys($this->toArray());
    }

    public function set($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    public function add(array $parameters)
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    public function remove($key)
    {
        unset($this->parameters[$key]);
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    public function toArray(): array
    {
        return $this->parameters;
    }

    public function getAlpha($key, $default = '')
    {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
    }

    public function getAlnum($key, $default = '')
    {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
    }

    public function getDigits($key, $default = '')
    {
        // we need to remove - and + because they're allowed in the filter
        return str_replace(['-', '+'], '', $this->filter($key, $default, FILTER_SANITIZE_NUMBER_INT));
    }

    public function getInt($key, $default = 0): int
    {
        return (int)$this->get($key, $default);
    }

    public function getBoolean($key, $default = false): bool
    {
        return $this->filter($key, $default, FILTER_VALIDATE_BOOLEAN);
    }

    public function filter($key, $default = null, $filter = FILTER_DEFAULT, $options = [])
    {
        $value = $this->get($key, $default);

        // Always turn $options into an array - this allows filter_var option shortcuts.
        if (!is_array($options) && $options) {
            $options = ['flags' => $options];
        }

        // Add a convenience check for arrays.
        if (is_array($value) && !isset($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }

        return filter_var($value, $filter, $options);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->parameters);
    }

    public function count(): int
    {
        return count($this->parameters);
    }

    public function empty(): bool
    {
        return $this->parameters === [];
    }
}