<?php


namespace Ipuppet\Jade\Component\Router\Reason;


use Exception;
use Ipuppet\Jade\Component\Http\Response;
use Ipuppet\Jade\Component\Kernel\Config\Config;
use Psr\Log\LoggerInterface;

abstract class Reason implements ReasonInterface
{
    /**
     * @var string|null
     */
    protected ?string $content;

    private ?LoggerInterface $logger;

    /**
     * Reason constructor.
     * @param ?Config $config
     * @param ?LoggerInterface $logger
     * @throws Exception
     */
    public function __construct(Config $config = null, LoggerInterface $logger = null)
    {
        if ($logger) $this->logger = $logger;
        if ($config !== null) {
            $httpStatus = $this->getHttpStatus();
            $content = $config->get($httpStatus, false);
            if ($content) {
                switch ($content[0]) {
                    case '@': // 项目路径
                        $content = str_replace('@', $config->get('rootPath'), $content);
                        $this->setContent($this->getFileContent($content));
                        break;
                    default:
                        $this->setContent($content);
                }
            }
        }
    }

    public function getHttpStatus(): int
    {
        return Response::HTTP_200;
    }

    /**
     * @throws Exception
     */
    private function getFileContent($path): string
    {
        if (file_exists($path)) {
            return file_get_contents($path);
        } else {
            $message = "您在response配置文件中设定的文件 [$path] 不存在，请检查。";
            $this->logger?->warning($message);
            throw new Exception($message);
        }
    }

    public function getContent(): string
    {
        return $this->content ?? $this->getDefaultContent();
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    abstract public function getDefaultContent(): string;

    public function getDescription(): string
    {
        return $this->getDefaultContent();
    }
}