<?php


namespace Jade\Component\Router\Reason;


use Jade\Component\Http\Response;

class MethodNotAllow extends Reason implements ReasonInterface
{
    public function getHttpStatus(): int
    {
        return Response::HTTP_403;
    }

    public function getDefaultContent(): string
    {
        return 'Method Not Allow';
    }
}