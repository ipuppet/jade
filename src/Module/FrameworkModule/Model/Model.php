<?php


namespace Ipuppet\Jade\Module\FrameworkModule\Model;


use AppKernel;
use DateTime;
use DateTimeZone;
use Exception;
use PDO;
use Psr\Log\LoggerInterface;
use Ipuppet\Jade\Component\DatabaseDriver\PdoDatabaseDriver;
use Ipuppet\Jade\Component\Kernel\Config\ConfigLoader;
use Ipuppet\Jade\Foundation\Parser\JsonParser;
use Ipuppet\Jade\Component\Kernel\Kernel;
use Ipuppet\Jade\Component\Logger\Logger;
use Ipuppet\Jade\Foundation\Parameter\Parameter;
use Ipuppet\Jade\Foundation\Parameter\ParameterInterface;
use Ipuppet\Jade\Foundation\Path\Exception\PathException;

abstract class Model
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var ConfigLoader
     */
    private $configLoader;

    /**
     * 数据库连接信息
     * @var ParameterInterface
     */
    private $database;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * Model constructor.
     * @throws PathException
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @throws PathException
     */
    private function init()
    {
        $this->kernel = new AppKernel();
        $this->configLoader = $this->kernel->getConfigLoader()
            ->setParser(new JsonParser());
        $this->logger = new Logger();
        $this->logger->setName('PdoDatabaseDriver')
            ->setOutput($this->kernel->getLogPath());
        $this->database = new Parameter($this->configLoader
            ->setName('database')
            ->loadFromFile()
            ->all());
    }

    /**
     * @return Kernel
     */
    public function getKernel(): Kernel
    {
        return $this->kernel;
    }

    /**
     * @param $name
     * @return ParameterInterface
     */
    protected function getConfigByName($name): ParameterInterface
    {
        return new Parameter($this->configLoader
            ->setName($name)
            ->loadFromFile()
            ->all()
        );
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param string $dbname
     * @param string $username
     * @param string $password
     * @return PDO|PdoDatabaseDriver
     */
    protected function getPdo(string $dbname = '', string $username = '', string $password = '')
    {
        if ($this->pdo === null) {
            if ($dbname !== '') {
                $this->database->set('dbname', $dbname);
            }
            if ($username !== '') {
                $this->database->set('username', $username);
            }
            if ($password !== '') {
                $this->database->set('password', $password);
            }
            $this->pdo = new PdoDatabaseDriver($this->logger, $this->database->all());
        }
        return $this->pdo;
    }

    /**
     * 获取当前日期
     * @param string $format 日期格式
     * @return string
     * @throws Exception
     */
    protected function dateNow(string $format = 'Y-m-d H:i:s')
    {
        if ($this->date === null) {
            $this->date = new DateTime();
            $this->date->setTimezone(new DateTimeZone('PRC'));
        }
        return $this->date->format($format);
    }
}