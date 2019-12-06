<?php


namespace Zimings\Jade\Foundation\Parser\Config;


use Zimings\Jade\Foundation\Parameter\Parameter;
use Zimings\Jade\Foundation\Path\PathInterface;

class JsonParser implements ParserInterface
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
     * @param string $name
     * @return ParserInterface
     */
    public function setName(string $name): ParserInterface
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param PathInterface $path
     * @return ParserInterface
     */
    public function setPath(PathInterface $path): ParserInterface
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return array
     */
    public function loadAsArray(): array
    {
        $file = $this->path . $this->name . '.json';
        $json = file_get_contents($file);
        $result = json_decode($json, JSON_OBJECT_AS_ARRAY);
        return $result ?? [];
    }

    /**
     * @return Parameter
     */
    public function loadAsParameter(): Parameter
    {
        return new Parameter($this->loadAsArray());
    }

    public function fileExists(): bool
    {
        return file_exists($this->path . $this->name . '.json');
    }
}