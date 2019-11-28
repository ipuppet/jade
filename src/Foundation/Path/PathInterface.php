<?php


namespace Zimings\Jade\Foundation\Path;


interface PathInterface
{
    public function set(string $path);

    public function get(): string;

    public function __toString();

    /**
     * 将路径加入到当前路径后面
     * @param PathInterface $path
     * @return string
     */
    public function after(PathInterface $path = null);

    /**
     * 将路径加入到当前路径前面
     * @param PathInterface $path
     * @return string
     */
    public function before(PathInterface $path = null);
}