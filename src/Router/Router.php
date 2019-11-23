<?php


namespace Jade\Router;


class Router
{
    private $request;

    public function __construct($request)
    {
        $this->setRequest($request);
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }
}