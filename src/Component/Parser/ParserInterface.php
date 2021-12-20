<?php


namespace Ipuppet\Jade\Component\Parser;


use Ipuppet\Jade\Component\Parameter\Parameter;
use Ipuppet\Jade\Component\Path\PathInterface;

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
     * @return Parameter
     */
    public function loadAsParameter(): Parameter;

    /**
     * @return bool
     */
    public function fileExists(): bool;
}
