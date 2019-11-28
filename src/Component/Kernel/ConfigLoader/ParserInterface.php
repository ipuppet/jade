<?php


namespace Zimings\Jade\Component\Kernel\ConfigLoader;


use Zimings\Jade\Foundation\Path\PathInterface;

interface ParserInterface
{
    /**
     * @param string $name
     * @return ParserInterface
     */
    public function setName(string $name): ParserInterface;

    /**
     * @param PathInterface $path
     * @return ParserInterface
     */
    public function setPath(PathInterface $path): ParserInterface;

    /**
     * @return array
     */
    public function loadAsArray(): array;

    /**
     * @return object
     */
    public function loadAsObject();
}