<?php


namespace Jade\Http;


final class Header
{
    private $headers = [];

    public function __construct(array $headers = [])
    {
        if ($headers !== []) {
            $this->add($headers);
        }
    }

    public function set(string $key, $values)
    {
        $values = array_values((array)$values);
        $this->headers[$key] = $values;
    }

    public function add(array $headers)
    {
        foreach ($headers as $key => $values) {
            $this->set($key, $values);
        }
    }

    public function remove($key)
    {
        unset($this->headers[$key]);
    }

    public function has($key)
    {
        return array_key_exists($key, $this->all());
    }

    public function get($key)
    {
        return $this->headers[$key];
    }

    public function all()
    {
        return $this->headers;
    }
}