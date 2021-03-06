<?php


namespace Ipuppet\Jade\Foundation\Parser;


use Ipuppet\Jade\Foundation\Parameter\Parameter;
use Ipuppet\Jade\Foundation\Path\PathInterface;

class JsonParser implements ParserInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var PathInterface
     */
    private PathInterface $path;

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