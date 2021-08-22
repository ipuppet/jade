<?php

namespace App;

use Ipuppet\Jade\Component\Kernel\Kernel;
use Ipuppet\Jade\Foundation\Path\Exception\PathException;
use Ipuppet\Jade\Foundation\Path\Path;
use Ipuppet\Jade\Foundation\Path\PathInterface;

class AppKernel extends Kernel
{
    /**
     * 获取项目根目录
     * @return PathInterface
     * @throws PathException
     */
    public function getRootPath(): PathInterface
    {
        if ($this->rootPath === null) {
            $this->rootPath = new Path(dirname(__DIR__));
        }
        return $this->rootPath;
    }
}
