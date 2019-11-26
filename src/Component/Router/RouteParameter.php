<?php


namespace Jade\Component\Router;


class RouteParameter
{
    /**
     * @var array
     */
    protected $parameters = [];

    public function __construct($parameters)
    {
        $this->setParameters($parameters);
    }

    public function setParameters($parameters)
    {
    }
}