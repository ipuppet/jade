<?php


namespace Jade\Foundation;


use Jade\Foundation\Exception\PathSeparatorException;
use Symfony\Component\Dotenv\Exception\PathException;

class Path
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $separator;

    public function __construct(string $path = '')
    {
        $this->set($path);
    }

    public function set(string $path)
    {
        if (is_file($path)) {
            throw new PathException('路径不能是文件');
        }
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function get(): string
    {
        if ($this->path !== '' && $this->path[strlen($this->path) - 1] !== $this->getSeparator()) {
            $this->path = $this->path . $this->getSeparator();
        }
        return $this->path;
    }

    public function __toString()
    {
        return $this->get();
    }

    public function getSeparator()
    {
        if ($this->separator === null) {
            if (strpos($this->path, '/')) {
                $this->separator = '/';
            } elseif (strpos($this->path, '\\')) {
                $this->separator = '\\';
            } else {
                //默认
                $this->separator = '/';
            }
        }
        return $this->separator;
    }

    /**
     * 合并两个路径
     * @param Path $before
     * @param Path $after
     * @return string
     * @throws PathSeparatorException
     */
    public static function join(self $before = null, self $after = null)
    {
        if ($before === null || $after === null)
            return $before ?? $after;
        if ($before->getSeparator() != $after->getSeparator()) {
            throw new PathSeparatorException('目标路径与该路径分隔符不同');
        }
        if ($before[strlen($before) - 1] === $after[0]) {
            return $before . substr($after, 1);
        }
        return $before . $after;
    }

    /**
     * 将路径加入到当前路径后面
     * @param Path $path
     * @return string
     * @throws PathSeparatorException
     */
    public function after(self $path = null)
    {
        return self::join($this, $path);
    }

    /**
     * 将路径加入到当前路径前面
     * @param Path $path
     * @return string
     * @throws PathSeparatorException
     */
    public function before(self $path = null)
    {
        return self::join($path, $this);
    }
}