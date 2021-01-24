<?php


namespace Ipuppet\Jade\Component\Logger;


use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Ipuppet\Jade\Foundation\Path\PathInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;

class Logger extends AbstractLogger
{
    const OUTPUT_STDERR = 'php://stderr';
    const OUTPUT_STDOUT = 'php://stdout';
    const DATE_FORMAT = 'Y-m-d H:i:s';
    const DATE_TIME_ZONE = 'PRC';

    private string $name;

    private $handle;

    private $formatter;

    private bool $outputStatus = false;

    /**
     * Logger constructor.
     * @param string $name
     * @param $formatter
     */
    public function __construct(string $name = 'Log', callable $formatter = null)
    {
        $this->name = $name;
        $this->formatter = $formatter ?: [$this, 'format'];
    }

    public function setName($name): self
    {
        $this->name = $name;
        $this->outputStatus = false;
        return $this;
    }

    /**
     * @param $output
     * @return $this
     */
    public function setOutput($output): self
    {
        if ($output instanceof PathInterface) {
            if (!is_dir($output)) {
                mkdir($output, 0777, true);
            }
            $output .= $this->name . '.log';
        }
        if (false === $this->handle = is_resource($output) ? $output : @fopen($output, 'a')) {
            throw new InvalidArgumentException(sprintf('Unable to open "%s".', $output));
        }
        $this->outputStatus = true;
        return $this;
    }

    public function checkOutput(): bool
    {
        return $this->outputStatus;
    }

    private function format($level, $message, array $context = []): string
    {
        if ($context !== []) {
            $replace = [];
            foreach ($context as $key => $val) {
                if (null === $val || is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))) {
                    $replace["{{$key}}"] = $val;
                } elseif ($val instanceof DateTimeInterface) {
                    $val->setTimezone(new DateTimeZone(self::DATE_TIME_ZONE));
                    $replace["{{$key}}"] = $val->format(self::DATE_FORMAT);
                } elseif (is_object($val)) {
                    $replace["{{$key}}"] = '[object ' . get_class($val) . ']';
                } else {
                    $replace["{{$key}}"] = '[' . gettype($val) . ']';
                }
            }
            $message = strtr($message, $replace);
        }
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(self::DATE_TIME_ZONE));
        return sprintf('[%s] %s.%s %s', $date->format(self::DATE_FORMAT), $this->name, strtoupper($level), $message) . PHP_EOL;
    }

    public function print($message, array $context = [])
    {
        echo "outputStatus: {$this->outputStatus}\n";
        echo "name: {$this->name}\n";
        echo "message before format: {$message}\n";
        echo "message after format: {$this->format($message, $context)}\n";
        echo "context: \n";
        foreach ($context as $key => $value) {
            echo "    [{$key}] {$value}\n";
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param $message
     * @param array $context
     * @return void
     * @throws Exception
     */
    public function log($level, $message, array $context = [])
    {
        if (!$this->checkOutput()) {
            throw new Exception('是否忘记调用 setOutput 方法了？');
        }
        $formatter = $this->formatter;
        fwrite($this->handle, $formatter($level, $message, $context));
    }
}