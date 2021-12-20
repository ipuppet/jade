<?php


namespace Ipuppet\Jade\Component\Kernel;


use Ipuppet\Jade\Component\Kernel\Exception\ConfigLoadException;
use Ipuppet\Jade\Component\Parameter\Parameter;
use Ipuppet\Jade\Component\Parameter\ParameterInterface;
use Ipuppet\Jade\Component\Parser\ParserInterface;
use Ipuppet\Jade\Component\Path\PathInterface;

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
     * @var ParameterInterface
     */
    private ParameterInterface $config;

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
            throw new ConfigLoadException('是否忘记设置 `name` 属性？');
        }
        if (!($this->parser instanceof ParserInterface)) {
            throw new ConfigLoadException('属性 `parser` 必须遵循接口: ParserInterface.');
        }
        if (!($this->path instanceof PathInterface)) {
            throw new ConfigLoadException('属性 `path` 必须遵循接口: PathInterface.');
        }
        return true;
    }

    /**
     * @return ParameterInterface
     */
    public function loadFromFile(): ParameterInterface
    {
        $this->parser->setFileName($this->name)
            ->setFilePath($this->path);
        if (!$this->parser->isEmpty()) {
            $this->config = new Parameter($this->parser->loadAsArray());
        }
        return $this->config;
    }
}
