<?php


namespace Zimings\Jade\Module\FrameworkModule\Controller;


use Zimings\Jade\Component\Http\RequestFactory;
use Zimings\Jade\Component\Router\Router;

class Controller
{
    protected $router;

    public function __construct()
    {
        $this->router = new Router(RequestFactory::createFromSuperGlobals());
    }

    public function get()
    {
        var_dump($this->router);
    }
}