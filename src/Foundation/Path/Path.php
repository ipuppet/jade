<?php


namespace Zimings\Jade\Foundation\Path;


use Zimings\Jade\Foundation\Path\Exception\PathException;

class Path implements PathInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * Path constructor.
     * @param string $path
     * @throws PathException
     */
    public function __construct(string $path = '')
    {
        try {
            $this->set($path);
        } catch (PathException $e) {
            throw $e;
        }
    }

    /**
     * @param string $path
     * @throws PathException
     */
    public function set(string $path)
    {
        if (is_file($path)) {
            throw new PathException('路径不能是文件');
        }
        $this->path = str_replace('\\', '/', $path);
    }

    /**
     * @return string
     */
    public function get(): string
    {
        if ($this->path !== '' && $this->path[strlen($this->path) - 1] !== '/') {
            $this->path = $this->path . '/';
        }
        return $this->path;
    }

    public function __toString()
    {
        return $this->get();
    }

    /**
     * 合并两个路径
     * @param PathInterface|string $before
     * @param PathInterface|string $after
     * @return string
     */
    public static function join($before = null, $after = null)
    {
        if ($before === null || $after === null)
            return $before ?? $after;
        if (mb_substr($before, -1, 1) === mb_substr($after, 0, 1)) {
            return $before . mb_substr($after, 1);
        }
        return $before . $after;
    }

    /**
     * 将路径加入到当前路径后面
     * @param PathInterface|string $path
     */
    public function after($path = null)
    {
        $this->path = self::join($this, $path);
    }

    /**
     * 将路径加入到当前路径前面
     * @param PathInterface|string $path
     */
    public function before($path = null)
    {
        $this->path = self::join($path, $this);
    }
}