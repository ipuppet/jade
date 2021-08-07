<?php


namespace Ipuppet\Jade\Component\Http;


use Exception;
use InvalidArgumentException;

class JsonResponse extends Response
{
    protected int $encodingOptions = JSON_UNESCAPED_UNICODE;

    private function __construct(string $content = '', int $httpStatus = self::HTTP_200, array $headers = [])
    {
        parent::__construct($content, $httpStatus, $headers);
    }

    /**
     * 从数组创建
     * @param array $content
     * @param int $httpStatus
     * @param array $headers
     * @return JsonResponse
     */
    public static function fromArray(array $content = [], int $httpStatus = self::HTTP_200, array $headers = []): JsonResponse
    {
        $response = new static('', $httpStatus, $headers);
        $response->setArray($content);
        return $response;
    }

    /**
     * 设置数组内容
     * @param array $content
     */
    public function setArray(array $content): void
    {
        if (defined('HHVM_VERSION')) {
            // HHVM does not trigger any warnings and let exceptions
            // thrown from a JsonSerializable object pass through.
            // If only PHP did the same...
            $content = json_encode($content, $this->encodingOptions);
        } else {
            if (!interface_exists('JsonSerializable', false)) {
                try {
                    $content = @json_encode($content, $this->encodingOptions);
                } finally {
                    restore_error_handler();
                }
            } else {
                try {
                    $content = json_encode($content, $this->encodingOptions);
                } catch (Exception $e) {
                    if ($this->hasLogger())
                        $this->logger->error($e->getMessage());
                }

                if (PHP_VERSION_ID >= 70300 && (JSON_THROW_ON_ERROR & $this->encodingOptions)) {
                    $this->setJson($content);
                }
            }
        }
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(json_last_error_msg());
        }
        $this->setJson($content);
    }

    /**
     * 设置Json字符串内容
     * @param string $json
     */
    public function setJson(string $json): void
    {
        $this->content = $json;
        // 防止覆盖自定义 Content-Type
        if (!$this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }
    }

    /**
     * 从Json字符串创建
     * @param string $content
     * @param int $httpStatus
     * @param array $headers
     * @return JsonResponse
     */
    public static function fromJsonString(string $content = '{}', int $httpStatus = self::HTTP_200, array $headers = []): JsonResponse
    {
        return new static($content, $httpStatus, $headers);
    }

    public function getEncodingOptions(): int
    {
        return $this->encodingOptions;
    }

    /**
     * 设置Json编码设置
     * @param int $encodingOptions
     * @return $this
     */
    public function setEncodingOptions(int $encodingOptions): self
    {
        $this->encodingOptions = $encodingOptions;
        return $this->setContent(json_decode($this->content));
    }
}