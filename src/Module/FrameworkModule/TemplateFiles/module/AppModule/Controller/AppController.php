<?php


namespace AppModule\Controller;


use Jade\Component\Http\Response;
use Jade\Module\FrameworkModule\Controller\Controller;

class AppController extends Controller
{
    /**
     * 由于可能会有token携带'*'即进行0次或多次匹配，所以可能存在空值(null)
     * 这时可以提供一个默认值
     * 您可以在您的方法中进行设置，还可以在路由中进行添加
     * 如下方的defaults属性
     *
     * {
     *   "methods": "get",
     *   "name": "App_say",
     *   "path": "/{name}",
     *   "tokens": {
     *     "name": "([a-zA-Z]*)"
     *   },
     *   "defaults": {
     *     "name": "World"
     *   },
     *   "_controller": "AppModule\\Controller\\AppController::sayAction"
     * }
     *
     * @param string $name
     * @return Response
     */
    public function sayAction($name)
    {
        return new Response("Hello {$name}!");
    }
}