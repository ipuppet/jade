<?php


namespace Ipuppet\Jade\Component\Router\Reason;


interface ReasonInterface
{
    public function getContent(): string;

    public function getHttpStatus(): int;

    public function getDescription(): string;
}