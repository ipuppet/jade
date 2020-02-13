<?php


namespace Zimings\Jade\Component\Router\Reason;


use Zimings\Jade\Component\Http\Response;

class MethodNotAllow extends Reason implements ReasonInterface
{
    public function getHttpStatus(): int
    {
        return Response::HTTP_405;
    }

    public function getDefaultContent(): string
    {
        return 'Method Not Allow';
    }
}