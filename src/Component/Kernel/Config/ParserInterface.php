<?php


namespace Zimings\Jade\Component\Kernel\Config;


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
     * @return Config
     */
    public function loadAsConfig(): Config;

    /**
     * @return bool
     */
    public function fileExists(): bool;
}