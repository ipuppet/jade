<?php

namespace App;

use Ipuppet\Jade\Component\Kernel\Kernel;
use Ipuppet\Jade\Component\Path\Exception\PathException;
use Ipuppet\Jade\Component\Path\Path;
use Ipuppet\Jade\Component\Path\PathInterface;

class AppKernel extends Kernel
{
    /**
     * 获取项目根目录
     * @return PathInterface
     * @throws PathException
     */
    public function getRootPath(): PathInterface
    {
        if (!isset($this->rootPath)) {
            $this->rootPath = new Path(dirname(__DIR__));
        }
        return $this->rootPath;
    }
}
