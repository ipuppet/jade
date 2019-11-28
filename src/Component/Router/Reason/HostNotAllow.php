<?php


namespace Zimings\Jade\Component\Router\Reason;


use Zimings\Jade\Component\Http\Response;

class HostNotAllow extends Reason implements ReasonInterface
{
    public function getHttpStatus(): int
    {
        return Response::HTTP_403;
    }

    public function getDefaultContent(): string
    {
        return 'Host Not Allow';
    }
}