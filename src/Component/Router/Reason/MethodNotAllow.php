<?php


namespace Jade\Component\Router\Reason;


use Jade\Component\Http\Response;

class MethodNotAllow implements ReasonInterface
{
    /**
     * @var string
     */
    private $content;

    public function __construct(string $content = 'Method Not Allow')
    {
        $this->content = $content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getHttpStatus(): int
    {
        return Response::HTTP_403;
    }
}