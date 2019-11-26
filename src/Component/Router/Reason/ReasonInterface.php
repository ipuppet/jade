<?php


namespace Jade\Component\Router\Reason;


interface ReasonInterface
{
    public function getContent(): string;

    public function getHttpStatus(): int;
}