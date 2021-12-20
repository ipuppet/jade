<?php


namespace Ipuppet\Jade\Component\Parser;


class JsonParser extends Parser implements ParserInterface
{
    public string $type = 'json';

    /**
     * @return array
     */
    public function loadAsArray(): array
    {
        $content = $this->getContent();
        return json_decode($content, JSON_OBJECT_AS_ARRAY) ?? [];
    }
}
