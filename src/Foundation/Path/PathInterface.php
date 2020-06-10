<?php


namespace Ipuppet\Jade\Foundation\Path;


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
    public static function join($before = null, $after = null): PathInterface;

    /**
     * 将路径加入到当前路径后面
     * @param PathInterface|string $path
     * @return PathInterface
     */
    public function after($path = null): PathInterface;

    /**
     * 将路径加入到当前路径前面
     * @param PathInterface|string $path
     * @return PathInterface
     */
    public function before($path = null): PathInterface;

    /**
     * 将文件名添加到路径最后
     * @param string $file
     * @return PathInterface
     */
    public function setFile(string $file): PathInterface;
}