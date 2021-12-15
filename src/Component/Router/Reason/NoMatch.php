<?php


namespace Ipuppet\Jade\Component\Router\Reason;


use Ipuppet\Jade\Component\Http\Response;

class NoMatch extends Reason implements ReasonInterface
{
    public function getDefaultHttpStatus(): int
    {
        return Response::HTTP_404;
    }

    public function getDefaultContent(): string
    {
        return 'Not Found';
    }
}
