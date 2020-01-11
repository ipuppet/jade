<?php


namespace Zimings\Jade\Component\Http;


use Exception;
use InvalidArgumentException;

class JsonResponse extends Response
{
    protected $data;
    protected $callback;
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

    public function setEncodingOptions($encodingOptions)
    {
        $this->encodingOptions = (int)$encodingOptions;

        return $this->setData(json_decode($this->data));
    }

    public function setCallback($callback = null)
    {
        if (null !== $callback) {
            // partially taken from https://geekality.net/2011/08/03/valid-javascript-identifier/
            // partially taken from https://github.com/willdurand/JsonpCallbackValidator
            //      JsonpCallbackValidator is released under the MIT License. See https://github.com/willdurand/JsonpCallbackValidator/blob/v1.1.0/LICENSE for details.
            //      (c) William Durand <william.durand1@gmail.com>
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*(?:\[(?:"(?:\\\.|[^"\\\])*"|\'(?:\\\.|[^\'\\\])*\'|\d+)\])*?$/u';
            $reserved = [
                'break', 'do', 'instanceof', 'typeof', 'case', 'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 'for', 'switch', 'while',
                'debugger', 'function', 'this', 'with', 'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 'extends', 'super', 'const', 'export',
                'import', 'implements', 'let', 'private', 'public', 'yield', 'interface', 'package', 'protected', 'static', 'null', 'true', 'false',
            ];
            $parts = explode('.', $callback);
            foreach ($parts as $part) {
                if (!preg_match($pattern, $part) || in_array($part, $reserved, true)) {
                    throw new InvalidArgumentException('The callback name is not valid.');
                }
            }
        }

        $this->callback = $callback;

        return $this->update();
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
        if (null !== $this->callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript');

            return $this->setContent(sprintf('/**/%s(%s);', $this->callback, $this->data));
        }

        // Only set the header when there is none or when it equals 'text/javascript' (from a previous update with callback)
        // in order to not overwrite a custom definition.
        if (!$this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }

        return $this->setContent($this->data);
    }
}