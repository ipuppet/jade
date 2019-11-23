<?php


namespace Jade\Logger;
include '../../vendor/autoload.php';

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Jade\Foundation\Path;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;

class Logger extends AbstractLogger
{
    const OUTPUT_STDERR = 'php://stderr';
    const OUTPUT_STDOUT = 'php://stdout';
    const DATE_FORMAT = 'Y-m-d H:i:s';
    const DATE_TIME_ZONE = 'PRC';

    private $name;

    private $handle;

    private $formatter;

    private $output;

    /**
     * Logger constructor.
     * @param string $name
     * @param $formatter
     */
    public function __construct(string $name, callable $formatter = null)
    {
        $this->name = $name;
        $this->formatter = $formatter ?: [$this, 'format'];
    }

    /**
     * @param $output
     * @return $this
     */
    public function setOutput($output)
    {
        if ($output instanceof Path) {
            $output .= $this->name . '.log';
        }
        if (false === $this->handle = is_resource($output) ? $output : @fopen($output, 'a')) {
            throw new InvalidArgumentException(sprintf('Unable to open "%s".', $output));
        }
        return $this;
    }

    private function format($level, $message, array $context = [])
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
        return sprintf('[%s] %s.%s %s', $date->format(self::DATE_FORMAT), $this->name, $level, $message) . PHP_EOL;
    }

    public function print($message, array $context = [])
    {
        echo "output: {$this->output}\n";
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
     * @param string $message
     * @param array $context
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function log($level, $message, array $context = [])
    {
        $formatter = $this->formatter;
        fwrite($this->handle, $formatter($level, $message, $context));
    }
}

$logger = new Logger('test');
$logger->setOutput(new Path('.'))
    ->info('info test');
$logger->debug('debug test');
$logger->warning('warning test');
$logger->notice('notice test');
$logger->alert('alert test');
$logger->emergency('emergency test');

$logger->setOutput(Logger::OUTPUT_STDOUT)
    ->info('info test');
$logger->debug('debug test');
$logger->warning('warning test');
$logger->notice('notice test');
$logger->alert('alert test');
$logger->emergency('emergency test');