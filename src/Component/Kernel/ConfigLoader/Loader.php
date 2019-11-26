<?php


namespace Jade\Component\Kernel\ConfigLoader;


use Jade\Component\Kernel\ConfigLoader\Exception\ConfigLoaderException;
use Jade\Foundation\Path\PathInterface;

class Loader
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var PathInterface
     */
    private $path;

    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var array
     */
    private $config;

    public function __construct(string $name = '', PathInterface $path = null, ParserInterface $parser = null)
    {
        $this->name = $name;
        $this->path = $path;
        $this->parser = $parser;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setPath(PathInterface $path)
    {
        $this->path = $path;
        return $this;
    }

    public function setParser(ParserInterface $parser)
    {
        $this->parser = $parser;
        return $this;
    }

    /**
     * @return bool
     * @throws ConfigLoaderException
     */
    public function prepare(): bool
    {
        if ($this->name === '') {
            throw new ConfigLoaderException('是否忘记设置name属性？');
        }
        if (!($this->parser instanceof ParserInterface)) {
            throw new ConfigLoaderException('parser属性必须instanceof ParserInterface');
        }
        if (!($this->path instanceof PathInterface)) {
            throw new ConfigLoaderException('path属性必须instanceof PathInterface');
        }
        if ($this->config === null) {
            $this->loadFromFile();
        }
        return true;
    }

    public function loadFromFile()
    {
        $this->config = $this->parser
            ->setName($this->name)
            ->setPath($this->path)
            ->loadAsArray();
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     * @param array $config
     */
    public function add(array $config = [])
    {
        $this->config = array_replace($this->config, $config);
    }

    /**
     * @param $key
     */
    public function remove($key)
    {
        unset($this->config[$key]);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->config);
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     * @throws ConfigLoaderException
     */
    public function get($key, $default = null)
    {
        if ($this->prepare())
            return $this->config[$key] ?? $default;
        return false;
    }

    /**
     * @return array
     * @throws ConfigLoaderException
     */
    public function all()
    {
        if ($this->prepare())
            return $this->config;
        return [];
    }
}