<?php


use Jade\Component\Kernel\Kernel;
use Jade\Foundation\Path\Exception\PathException;
use Jade\Foundation\Path\PathInterface;

class AppKernel extends Kernel
{
    /**
     * 获取缓存目录
     * @return PathInterface
     * @throws PathException
     */
    public function getCacheDir(): PathInterface
    {
        return $this->createPath($this->getRootDir() . "/var/cache");
    }

    /**
     * 获取日志目录
     * @return PathInterface
     * @throws PathException
     */
    public function getLogDir(): PathInterface
    {
        return $this->createPath($this->getRootDir() . "/var/log");
    }

    /**
     * 获取项目根目录
     * @return PathInterface
     * @throws PathException
     */
    public function getRootDir(): PathInterface
    {
        return $this->createPath(dirname(__DIR__));
    }
}