<?php


namespace Jade\Component\Http;


use Jade\Foundation\Parameter;

class Server extends Parameter
{
    public function getHeaders()
    {
        $headers = [];
        // CONTENT_* 无 HTTP_ 前缀
        $contentHeaders = ['CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true];
        foreach ($this->parameters as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif (isset($contentHeaders[$key])) {
                $headers[$key] = $value;
            }
        }
        return $headers;
    }
}