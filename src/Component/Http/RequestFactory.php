<?php


namespace Ipuppet\Jade\Component\Http;


use Ipuppet\Jade\Foundation\Parameter\Parameter;

class RequestFactory
{
    public static function createFromSuperGlobals(): Request
    {
        $server = $_SERVER;
        if ('cli-server' === PHP_SAPI) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                $server['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $server['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
        }
        if (array_key_exists('CONTENT_TYPE', $server) && in_array('application/json', explode(';', $server['CONTENT_TYPE']))) {
            $post = file_get_contents('php://input');
            $post = json_decode($post, JSON_OBJECT_AS_ARRAY);
        } else {
            $post = $_POST;
        }
        $request = self::create($_GET, $post, [], $_COOKIE, $_FILES, $_SERVER);
        if (str_starts_with($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new Parameter($data);
        }
        return $request;
    }

    public static function create(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null): Request
    {
        return new Request($query, $request, $attributes, $cookies, $files, $server, $content);
    }
}