<?php


namespace AppModule\Controller;


use AppModule\Model\HelloModel;
use Zimings\Jade\Component\Http\Response;
use Zimings\Jade\Component\Kernel\Config\Exception\ConfigLoadException;
use Zimings\Jade\Foundation\Path\Exception\PathException;
use Zimings\Jade\Module\FrameworkModule\Controller\Controller;

class HelloController extends Controller
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
     * @throws ConfigLoadException
     * @throws PathException
     */
    public function sayAction($name)
    {
        $helloModel = new HelloModel();
        $tip = "You can change the message by changing the url<br>e.g. http://your.host.com/yourname";
        $message = "Hello {$name}! I am {$helloModel->getName()}.";
        return new Response($message . '<br>' . $tip);
    }
}