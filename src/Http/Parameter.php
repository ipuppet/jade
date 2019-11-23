<?php


namespace Jade\Http;


class Parameter
{
    protected $parameters;

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    public function set($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    public function add(array $parameters = [])
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    public function remove($key)
    {
        unset($this->parameters[$key]);
    }

    public function has($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    public function all()
    {
        return $this->parameters;
    }
}