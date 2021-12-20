<?php


namespace Ipuppet\Jade\Module\FrameworkModule\Controller;


use Ipuppet\Jade\Component\Kernel\Kernel;
use Ipuppet\Jade\Component\Http\Response;
use Ipuppet\Jade\Component\Logger\Logger;
use Ipuppet\Jade\Component\Parameter\Parameter;
use Ipuppet\Jade\Component\Parameter\ParameterInterface;

abstract class Controller
{
    public bool $isResponseBeforeController = false;
    public Response $response;
    /**
     * @var ?ParameterInterface
     */
    private ?ParameterInterface $corsConfig = null;
    /**
     * @var Kernel
     */
    protected Kernel $kernel;
    /**
     * @var Logger
     */
    protected Logger $logger;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->logger = new Logger();
        $this->logger->setName('Controller')
            ->setOutput($this->kernel->getLogPath());
    }

    /**
     * @param Parameter $config
     */
    public function setCorsConfig(Parameter $config): void
    {
        isset($this->corsConfig) ? $this->corsConfig->add($config->toArray()) : $this->corsConfig = $config;
    }

    public function checkCors(): bool
    {
        // 未发送 HTTP_ORIGIN
        if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) return false;
        if (null === $this->corsConfig) $this->corsConfig = new Parameter();
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
