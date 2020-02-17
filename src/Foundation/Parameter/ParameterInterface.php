<?php


namespace Ipuppet\Jade\Foundation\Parameter;


interface ParameterInterface
{
    public function set($key, $value);

    public function add(array $parameters = []);

    public function remove($key);

    public function has($key);

    public function get($key, $default = null);

    public function all();

    public function getAlpha($key, $default = '');

    public function getAlnum($key, $default = '');

    public function getDigits($key, $default = '');

    public function getInt($key, $default = 0);

    public function getBoolean($key, $default = false);

    public function getIterator();

    public function count();

    public function empty(): bool;
}