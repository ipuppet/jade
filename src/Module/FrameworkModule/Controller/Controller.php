<?php


namespace Ipuppet\Jade\Module\FrameworkModule\Controller;


use Ipuppet\Jade\Component\Http\Response;

abstract class Controller
{
    /**
     * 忽略的请求方法
     * @var bool
     */
    protected bool $isIgnoreRequest = false;

    /**
     * @var ?Response
     */
    protected ?Response $defaultResponse = null;

    public function isIgnoreRequest(): bool
    {
        return $this->isIgnoreRequest;
    }

    protected function ignoreRequest(): Controller
    {
        $this->isIgnoreRequest = true;
        return $this;
    }

    /**
     * @return Response
     */
    public function getDefaultResponse(): Response
    {
        if (!($this->defaultResponse instanceof Response)) {
            $this->defaultResponse = Response::create('', Response::HTTP_204);
        }
        return $this->defaultResponse;
    }

    /**
     * @param Response $defaultResponse
     * @return Controller
     */
    protected function setDefaultResponse(Response $defaultResponse): Controller
    {
        $this->defaultResponse = $defaultResponse;
        return $this;
    }
}