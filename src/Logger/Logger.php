<?php


namespace Jade\Logger;


use DateTime;
use Exception;
use Jade\Kernel\Path;
use Jade\Logger\Exception\LoggerException;

class Logger
{
    const DEFAULT_ERROR_CODE = 0;
    const LEVEL_INFO = 'INFO';
    const LEVEL_NOTE = 'NOTE';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';

    /**
     * @var Path
     */
    protected $basePath;

    /**
     * @var Path
     */
    protected $path;

    protected $level;

    protected $name;

    /**
     * @var array
     */
    protected $lines;

    /**
     * Logger constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->setName($name);
        $this->lines = [];
    }

    /**
     * @throws LoggerException
     * @throws Exception
     */
    public function __destruct()
    {
        if ($this->lines !== []) {
            try {
                $this->write();
            } catch (LoggerException $e) {
                throw $e;
            } catch (Exception $e) {
                throw $e;
            }
        }
    }

    protected function setLevel($level)
    {
        $this->level = $level;
    }

    protected function setByString(string $string)
    {
        $this->addToLines($string, self::DEFAULT_ERROR_CODE);
    }

    protected function setByException(Exception $e)
    {
        $this->addToLines($e->getMessage(), $e->getCode() ?? self::DEFAULT_ERROR_CODE);
    }

    protected function setByArray(array $array)
    {
        if (key_exists('code', $array) && key_exists('message', $array)) {
            $code = $array['code'];
            $message = $array['message'];
        } else {
            if (is_numeric($array[0])) {
                $code = $array[0];
                $message = $array[1];
            } else {
                $code = $array[1];
                $message = $array[0];
            }
        }
        $this->addToLines($message, $code);
    }

    protected function addToLines($message, $code)
    {
        $date = new DateTime();
        $line = "[{$date->format('Y-m-d H:i:s')}] {$this->name}.{$this->level} $message [{$code}]";
        $this->lines[] = $line;
    }

    /**
     * 设置日志记录器名字
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param Path $path
     * @return $this
     */
    public function setPath(Path $path)
    {
        $this->path = $path;
        $this->path .= $this->name . '.log';
        return $this;
    }

    /**
     * @return $this
     * @throws LoggerException
     * @throws Exception
     */
    public function write()
    {
        if (empty($this->path)) {
            throw new LoggerException('日志路径为空');
        }
        if (empty($this->lines)) {
            return $this;
        }
        $handle = fopen($this->path, 'a');
        try {
            $str = '';
            foreach ($this->lines as $line) {
                $str .= $line . "\n";
            }
            fwrite($handle, $str);
            $this->lines = [];
        } catch (Exception $e) {
            throw $e;
        } finally {
            fclose($handle);
        }
        return $this;
    }

    /**
     * @param $level
     * @param $content
     * @param null $closure
     */
    protected function set($level, $content, $closure = null)
    {
        $this->setLevel($level);
        if ($closure === null) {
            if ($content instanceof Exception) {
                $this->setByException($content);
            } else if (is_array($content)) {
                $this->setByArray($content);
            } else if (is_string($content)) {
                $this->setByString($content);
            }
        } else {
            /** @var string $closure */
            $closure();
        }
    }

    /**
     * @param $info
     * @return $this
     */
    public function setInfo($info)
    {
        $this->set(self::LEVEL_INFO, $info);
        return $this;
    }

    /**
     * @param $note
     * @return $this
     */
    public function setNote($note)
    {
        $this->set(self::LEVEL_NOTE, $note);
        return $this;
    }

    /**
     * @param $warning
     * @return $this
     */
    public function setWarning($warning)
    {
        $this->set(self::LEVEL_WARNING, $warning);
        return $this;
    }

    /**
     * @param $error
     * @return $this
     */
    public function setError($error)
    {
        $this->set(self::LEVEL_ERROR, $error);
        return $this;
    }

    public function debug()
    {
        echo '$this->path: ', $this->path . "\n";
        echo '$this->level: ', $this->level . "\n";
        echo '$this->lines: ' . "\n";
        foreach ($this->lines as $line) {
            echo '    ', $line . "\n";
        }
    }
}