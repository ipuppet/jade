<?php


use Zimings\Jade\Component\Kernel\Kernel;
use Zimings\Jade\Foundation\Path\Exception\PathException;
use Zimings\Jade\Foundation\Path\PathInterface;

class AppKernel extends Kernel
{
    /**
     * 获取缓存目录
     * @return PathInterface
     * @throws PathException
     */
    public function getCachePath(): PathInterface
    {
        return $this->getRootPath()->after($this->createPath('/var/cache'));
    }

    /**
     * 获取日志目录
     * @return PathInterface
     * @throws PathException
     */
    public function getLogPath(): PathInterface
    {
        return $this->getRootPath()->after($this->createPath('/var/log'));
    }

    /**
     * 获取项目根目录
     * @return PathInterface
     * @throws PathException
     */
    public function getRootPath(): PathInterface
    {
        return $this->createPath(dirname(__DIR__));
    }
}