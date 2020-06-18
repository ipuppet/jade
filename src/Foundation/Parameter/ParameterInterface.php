<?php


namespace Ipuppet\Jade\Foundation\Parameter;


use ArrayIterator;

interface ParameterInterface
{
    public function keys(): array;

    public function set($key, $value);

    public function add(array $parameters);

    public function remove($key);

    public function has($key): bool;

    public function get($key, $default = null);

    public function toArray(): array;

    public function getAlpha($key, $default = '');

    public function getAlnum($key, $default = '');

    public function getDigits($key, $default = '');

    public function getInt($key, $default = 0): int;

    public function getBoolean($key, $default = false): bool;

    public function getIterator(): ArrayIterator;

    public function count(): int;

    public function empty(): bool;
}