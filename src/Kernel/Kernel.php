<?php


namespace Jade\Kernel;


abstract class Kernel
{
    /**
     * 获取缓存目录
     * @return Path
     */
    public function getCacheDir(): Path
    {
        return new Path($this->getRootDir() . "/var/cache");
    }

    /**
     * 获取日志目录
     * @return Path
     */
    public function getLogDir(): Path
    {
        return new Path($this->getRootDir() . "/var/log");
    }

    /**
     * 获取项目根目录
     * @return Path
     */
    public function getRootDir(): Path
    {
        return new Path(dirname(__DIR__));
    }
}