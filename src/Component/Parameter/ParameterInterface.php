<?php


namespace Ipuppet\Jade\Component\Parameter;


use ArrayIterator;

interface ParameterInterface
{
    public function keys(): array;

    public function set(string $key, $value): self;

    public function add(array $parameters): self;

    public function remove(string $key): self;

    public function has(string $key): bool;

    public function get(string $key, $default = null);

    public function toArray(): array;

    public function getAlpha(string $key, $default = '');

    public function getAlnum(string $key, $default = '');

    public function getDigits(string $key, $default = '');

    public function getInt(string $key, $default = 0): int;

    public function getBoolean(string $key, $default = false): bool;

    public function getIterator(): ArrayIterator;

    public function count(): int;

    public function empty(): bool;
}
