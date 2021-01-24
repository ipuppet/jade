<?php


namespace Ipuppet\Jade\Component\Kernel\Config;


use Ipuppet\Jade\Component\Kernel\Config\Exception\ConfigLoadException;
use Ipuppet\Jade\Foundation\Parser\ParserInterface;
use Ipuppet\Jade\Foundation\Path\PathInterface;

class ConfigLoader
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var ?PathInterface
     */
    private ?PathInterface $path;

    /**
     * @var ?ParserInterface
     */
    private ?ParserInterface $parser;

    /**
     * @var Config
     */
    private Config $config;

    public function __construct(string $name = '', PathInterface $path = null, ParserInterface $parser = null)
    {
        $this->name = $name;
        $this->path = $path;
        $this->parser = $parser;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setPath(PathInterface $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function setParser(ParserInterface $parser): self
    {
        $this->parser = $parser;
        return $this;
    }

    /**
     * @return bool
     * @throws ConfigLoadException
     */
    public function prepare(): bool
    {
        if ($this->name === '') {
            throw new ConfigLoadException('是否忘记设置name属性？');
        }
        if (!($this->parser instanceof ParserInterface)) {
            throw new ConfigLoadException('parser属性必须instanceof ParserInterface');
        }
        if (!($this->path instanceof PathInterface)) {
            throw new ConfigLoadException('path属性必须instanceof PathInterface');
        }
        return true;
    }

    /**
     * @return Config
     */
    public function loadFromFile(): Config
    {
        $this->parser->setName($this->name)
            ->setPath($this->path);
        if ($this->parser->fileExists()) {
            $this->config = new Config($this->parser->loadAsArray());
        }
        return $this->config;
    }
}