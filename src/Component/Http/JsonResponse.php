<?php


namespace Ipuppet\Jade\Component\Http;


use Exception;
use InvalidArgumentException;

class JsonResponse extends Response
{
    protected $data;
    protected $encodingOptions = JSON_UNESCAPED_UNICODE;

    public function __construct($data = null, int $httpStatus = self::HTTP_200, array $headers = [], $json = false)
    {
        parent::__construct($data, $httpStatus, $headers);
        $json ? $this->setJson($data) : $this->setData($data);
    }

    public static function create($data = null, int $httpStatus = self::HTTP_200, array $headers = [])
    {
        return new static($data, $httpStatus, $headers);
    }

    public static function fromJsonString($data = null, int $httpStatus = self::HTTP_200, array $headers = [])
    {
        return new static($data, $httpStatus, $headers, true);
    }

    public function getEncodingOptions()
    {
        return $this->encodingOptions;
    }

    public function setEncodingOptions(int $encodingOptions)
    {
        $this->encodingOptions = $encodingOptions;
        return $this->setData(json_decode($this->data));
    }

    public function setJson($json)
    {
        $this->data = $json;
        return $this->update();
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function setData($data = [])
    {
        if (defined('HHVM_VERSION')) {
            // HHVM does not trigger any warnings and let exceptions
            // thrown from a JsonSerializable object pass through.
            // If only PHP did the same...
            $data = json_encode($data, $this->encodingOptions);
        } else {
            if (!interface_exists('JsonSerializable', false)) {
                set_error_handler(function () {
                    return false;
                });
                try {
                    $data = @json_encode($data, $this->encodingOptions);
                } finally {
                    restore_error_handler();
                }
            } else {
                try {
                    $data = json_encode($data, $this->encodingOptions);
                } catch (Exception $e) {
                    if ($this->hasLogger())
                        $this->logger->error($e->getMessage());
                }

                if (PHP_VERSION_ID >= 70300 && (JSON_THROW_ON_ERROR & $this->encodingOptions)) {
                    return $this->setJson($data);
                }
            }
        }
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(json_last_error_msg());
        }
        return $this->setJson($data);
    }

    protected function update()
    {
        // 防止覆盖自定义 Content-Type
        if (!$this->headers->has('Content-Type') || $this->headers->get('Content-Type') === 'text/javascript') {
            $this->headers->set('Content-Type', 'application/json');
        }
        return $this->setContent($this->data);
    }
}