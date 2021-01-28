<?php


namespace Ipuppet\Jade\Component\Router\Reason;


use Exception;
use Ipuppet\Jade\Component\Http\Response;
use Ipuppet\Jade\Component\Kernel\Config\Config;
use Psr\Log\LoggerInterface;

abstract class Reason implements ReasonInterface
{
    /**
     * @var string
     */
    protected $content;

    /**
     * Reason constructor.
     * @param ?Config $config
     * @param ?LoggerInterface $logger
     * @throws Exception
     */
    public function __construct(Config $config = null, LoggerInterface $logger = null)
    {
        if ($config !== null) {
            $content = $config->get('errorResponse')[$this->getHttpStatus()];
            if ($content[0] === '@') {
                $content = str_replace('@', $config->get('rootPath'), $content);
                if (file_exists($content)) {
                    $this->content = file_get_contents($content);
                } else {
                    $message = '您在response配置文件中设定的文件不存在，请检查。';
                    if ($logger !== null) {
                        $logger->warning($message);
                    } else {
                        throw new Exception($message);
                    }
                }
            } else {
                $this->content = $content;
            }
        }
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content ?? $this->getDefaultContent();
    }

    abstract public function getDefaultContent(): string;

    public function getHttpStatus(): int
    {
        return Response::HTTP_200;
    }

    public function getDescription(): string
    {
        return $this->getDefaultContent();
    }
}