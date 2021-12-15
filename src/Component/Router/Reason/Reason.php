<?php


namespace Ipuppet\Jade\Component\Router\Reason;


abstract class Reason implements ReasonInterface
{
    /**
     * @var string|null
     */
    protected ?string $content;
    /**
     * @var integer
     */
    protected int $httpStatus;

    /**
     * @param callable $resolver 解析器，需返回数组 [0 => 状态码, 1 => 内容]
     */
    public function __construct(callable $resolver)
    {
        $result = $resolver($this->getDefaultHttpStatus());
        $this->httpStatus = $result[0];
        $this->content = $result[1] ?? $this->getDefaultContent();
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    abstract public function getDefaultHttpStatus(): int;
    abstract public function getDefaultContent(): string;

    public function getDescription(): string
    {
        return $this->getDefaultContent();
    }
}
