<?php


namespace Ipuppet\Jade\Component\Parser;


class YamlParser extends Parser implements ParserInterface
{
    public string $type = 'yaml';

    /**
     * @return array
     */
    public function loadAsArray(): array
    {
        $content = $this->getContent();
        return yaml_parse($content) ?? [];
    }
}
