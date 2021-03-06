<?php


namespace Ipuppet\Jade\Plugins;


use Psr\Log\LoggerInterface;

class HttpSender
{
    private ?LoggerInterface $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private function exec($ch, $method): array
    {
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $message = "Curl error: [{$method}] " . curl_error($ch);
            $response['error'] = $message;
            if ($this->logger) $this->logger->error($message);
        }
        curl_close($ch);
        if (!is_array($response)) {
            $response = json_decode($response, true) ?? [];
        }
        return $response;
    }

    public function get(string $url, array $options = []): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        foreach ($options as $option) {
            curl_setopt($ch, $option[0], $option[1]);
        }
        return $this->exec($ch, 'GET');
    }

    public function post(string $url, array $data = [], array $options = []): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        foreach ($options as $option) {
            curl_setopt($ch, $option[0], $option[1]);
        }
        return $this->exec($ch, 'POST');
    }
}