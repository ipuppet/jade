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
    public function setFileName(string $fileName): ParserInterface;

    /**
     * @param PathInterface $path
     * @return ParserInterface
     */
    public function setFilePath(PathInterface $filePath): ParserInterface;

    public function setContent(string $content): ParserInterface;

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
    public function isEmpty(): bool;
}
