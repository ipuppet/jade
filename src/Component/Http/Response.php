<?php


namespace Ipuppet\Jade\Component\Http;


use Psr\Log\LoggerInterface;

class Response
{
    const HTTP_200 = 200;
    const HTTP_201 = 201;
    const HTTP_204 = 204;
    const HTTP_400 = 400;
    const HTTP_403 = 403;
    const HTTP_404 = 404;
    const HTTP_405 = 405;
    const HTTP_500 = 500;

    protected static $httpStatusText = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error',
    ];

    protected $statusCode; //HTTP状态码
    protected $statusText; //状态码对应的提示字符
    protected $content; //响应内容
    protected $headers; //响应头
    protected $httpVersion = '1.1'; //HTTP版本

    /**
     * @var LoggerInterface
     */
    protected $logger; //日志记录器

    public function __construct($content = '', int $httpStatus = self::HTTP_200, array $headers = [])
    {
        $this->setContent($content);
        $this->setStatusCode($httpStatus);
        $this->headers = new Header($headers);
    }

    public function hasLogger(): bool
    {
        return $this->logger === null;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public static function create($content = '', int $httpStatus = self::HTTP_200, array $headers = [])
    {
        return new static($content, $httpStatus, $headers);
    }

    public function setHttpVersion(string $httpVersion)
    {
        $this->httpVersion = $httpVersion;
        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
        $this->statusText = self::$httpStatusText[$this->statusCode];
        return $this;
    }

    /**
     * 发送header
     * @return $this
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return $this;
        }
        foreach ($this->headers->toArray() as $name => $values) {
            foreach ($values as $value) {
                $name = implode('-', array_map('ucfirst', explode('-', $name)));
                header($name . ': ' . $value, false, $this->statusCode);
            }
        }
        header(sprintf('HTTP/%s %s %s', $this->httpVersion, $this->statusCode, $this->statusText), true, $this->statusCode);
        return $this;
    }

    public function send()
    {
        $this->sendHeaders();
        echo $this->content;
    }
}