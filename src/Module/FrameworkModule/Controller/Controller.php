<?php


namespace Ipuppet\Jade\Module\FrameworkModule\Controller;


use Ipuppet\Jade\Component\Kernel\Config\Config;

abstract class Controller
{
    /**
     * @var ?Config
     */
    private ?Config $corsConfig = null;

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
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] === 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_ORIGIN'] ?? $protocol . $_SERVER['HTTP_HOST'];
        // 判断是否允许跨域
        if (in_array($host, $this->corsConfig->get('hosts', []))) {
            $methods = ['OPTIONS'];
            foreach ($this->corsConfig->get('methods', ['get', 'post', 'put', 'delete']) as $method) {
                $methods[] = strtoupper($method);
            }
            $headers = $this->corsConfig->get('headers', ['Content-Type', 'Authorization']);
            header('Access-Control-Allow-Origin: ' . $host);
            header('Access-Control-Allow-Methods: ' . implode(', ', $methods));
            header('Access-Control-Allow-Headers: ' . implode(', ', $headers));
            return true;
        }
        return false;
    }
}