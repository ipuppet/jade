<?php


namespace Zimings\Jade\Module\FrameworkModule\Model;


use AppKernel;
use DateTime;
use DateTimeZone;
use Exception;
use PDO;
use Psr\Log\LoggerInterface;
use Zimings\Jade\Component\DatabaseDriver\PdoDatabaseDriver;
use Zimings\Jade\Component\Kernel\Config\ConfigLoader;
use Zimings\Jade\Component\Kernel\Config\Exception\ConfigLoadException;
use Zimings\Jade\Component\Kernel\Config\JsonParser;
use Zimings\Jade\Component\Kernel\Kernel;
use Zimings\Jade\Component\Logger\Logger;
use Zimings\Jade\Foundation\Parameter\Parameter;
use Zimings\Jade\Foundation\Parameter\ParameterInterface;
use Zimings\Jade\Foundation\Path\Exception\PathException;

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
     * @throws ConfigLoadException
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @throws ConfigLoadException
     * @throws PathException
     */
    private function init()
    {
        $this->kernel = new AppKernel();
        $this->configLoader = $this->kernel->getConfigLoader()
            ->setParser(new JsonParser());
        $this->logger = new Logger();
        $this->logger->setName('PdoDatabaseDriver')
            ->setOutput($this->kernel->getLogDir());
        $this->database = new Parameter($this->configLoader
            ->setName('database')
            ->loadFromFile()
            ->all());
    }

    /**
     * @param $name
     * @return ParameterInterface
     * @throws ConfigLoadException
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