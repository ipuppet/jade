<?php


namespace AppModule\Controller;


use Jade\Component\Http\Response;
use Jade\Module\FrameworkModule\Controller\Controller;

class AppController extends Controller
{
    public function sayAction($name = 'World')
    {
        return new Response("Hello {$name}!");
    }
}