<?php


namespace Ipuppet\Jade\Module\FrameworkModule\Controller;


use Ipuppet\Jade\Component\Http\Response;
use Ipuppet\Jade\Component\Kernel\Config\Config;

abstract class Controller
{
    /**
     * @var ?Config
     */
    private ?Config $corsConfig = null;

    public bool $isResponseBeforeController = false;
    public Response $response;

    /**
     * @param Config $config
     */
    public function setCorsConfig(Config $config): void
    {
        $this->corsConfig === null ? $this->corsConfig = $config : $this->corsConfig->add($config->toArray());
    }

    public function checkCors(): bool
    {
        if (null === $this->corsConfig) $this->corsConfig = new Config();
        $origin = $_SERVER['HTTP_ORIGIN'];
        // 判断是否允许跨域
        if (in_array($origin, $this->corsConfig->get('hosts', []))) {
            $methods = ['OPTIONS'];
            foreach ($this->corsConfig->get('methods', ['get', 'post', 'put', 'delete']) as $method) {
                $methods[] = strtoupper($method);
            }
            $headers = $this->corsConfig->get('headers', ['Content-Type', 'Authorization']);
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: ' . implode(', ', $methods));
            header('Access-Control-Allow-Headers: ' . implode(', ', $headers));
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isResponseBeforeController(): bool
    {
        return $this->isResponseBeforeController;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    protected function responseBeforeController(string $content, int $httpStatus): void
    {
        $this->isResponseBeforeController = true;
        $this->response = Response::create($content, $httpStatus);
    }
}