<?php


namespace Zimings\Jade\Foundation\Path;


interface PathInterface
{
    public function set(string $path);

    public function get(): string;

    public function __toString();

    /**
     * 合并两个路径
     * @param PathInterface|string $before
     * @param PathInterface|string $after
     * @return string
     */
    public static function join($before = null, $after = null);

    /**
     * 将路径加入到当前路径后面
     * @param PathInterface|string $path
     */
    public function after($path = null);

    /**
     * 将路径加入到当前路径前面
     * @param PathInterface|string $path
     */
    public function before($path = null);
}