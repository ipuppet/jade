<?php


namespace Zimings\Jade\Component\Router\Reason;


use Exception;
use Psr\Log\LoggerInterface;
use Zimings\Jade\Component\Http\Response;
use Zimings\Jade\Component\Kernel\Config\Config;

abstract class Reason implements ReasonInterface
{
    /**
     * @var string
     */
    protected $content;

    /**
     * Reason constructor.
     * @param Config|null $config
     * @param LoggerInterface|null $logger
     * @throws Exception
     */
    public function __construct(Config $config = null, LoggerInterface $logger = null)
    {
        if ($config !== null) {
            $content = $config->get($this->getHttpStatus());
            if ($content[0] === '@') {
                $content = str_replace('@', $config->get('root_dir'), $content);
                if (file_exists($content)) {
                    include $content;
                    //阻止返回默认值
                    $this->content = '';
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
}