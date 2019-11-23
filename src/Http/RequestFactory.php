<?php


namespace Jade\Http;


class RequestFactory
{
    public static function createFromSuperGlobals()
    {
        return new Request($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
    }
}