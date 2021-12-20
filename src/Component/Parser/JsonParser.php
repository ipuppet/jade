<?php


namespace Ipuppet\Jade\Component\Parser;


use Ipuppet\Jade\Component\Parameter\Parameter;
use Ipuppet\Jade\Component\Path\PathInterface;

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
     * @return Parameter
     */
    public function loadAsParameter(): Parameter
    {
        return new Parameter($this->loadAsArray());
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

    public function fileExists(): bool
    {
        return file_exists($this->path . $this->name . '.json');
    }
}
