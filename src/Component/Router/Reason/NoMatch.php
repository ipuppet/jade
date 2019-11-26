<?php


namespace Jade\Component\Router\Reason;


use Jade\Component\Http\Response;

class NoMatch extends Reason implements ReasonInterface
{
    public function getHttpStatus(): int
    {
        return Response::HTTP_404;
    }

    public function getDefaultContent(): string
    {
        return 'Not Found';
    }
}