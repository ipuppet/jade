<?php


namespace Jade\Component\Router\Reason;


use Jade\Component\Http\Response;

class NoMatch implements ReasonInterface
{
    /**
     * @var string
     */
    private $content;

    public function __construct(string $content = 'Not Found')
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
        return Response::HTTP_404;
    }
}