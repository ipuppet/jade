<?php


namespace Ipuppet\Jade\Foundation\Path;


use Ipuppet\Jade\Foundation\Path\Exception\PathException;

class Path implements PathInterface
{
    /**
     * @var string
     */
    private string $path;

    /**
     * @var string
     */
    private string $file = '';

    /**
     * Path constructor.
     * @param string $path
     * @throws PathException
     */
    public function __construct(string $path = '')
    {
        $this->set($path);
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
        return $this->path . $this->file;
    }

    public function __toString(): string
    {
        return $this->get();
    }

    /**
     * 合并两个路径
     * @param PathInterface|string $before
     * @param PathInterface|string $after
     * @return PathInterface
     * @throws PathException
     */
    public static function join($before = null, $after = null): PathInterface
    {
        if ($before === null || $after === null)
            return $before ?? $after;
        if (mb_substr($before, -1, 1) === mb_substr($after, 0, 1)) {
            return new self($before . mb_substr($after, 1));
        }
        return new self($before . $after);
    }

    /**
     * 将路径加入到当前路径后面
     * @param null $path
     * @return PathInterface
     * @throws PathException
     */
    public function after($path = null): PathInterface
    {
        $this->path = (string)self::join($this, $path);
        return $this;
    }

    /**
     * 将路径加入到当前路径前面
     * @param null $path
     * @return PathInterface
     * @throws PathException
     */
    public function before($path = null): PathInterface
    {
        $this->path = (string)self::join($path, $this);
        return $this;
    }

    /**
     * @param string $file
     * @return PathInterface
     */
    public function setFile(string $file): PathInterface
    {
        $this->file = $file;
        return $this;
    }
}