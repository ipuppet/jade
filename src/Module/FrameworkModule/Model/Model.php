<?php


namespace Ipuppet\Jade\Module\FrameworkModule\Model;


use App\AppKernel;
use DateTime;
use DateTimeZone;
use Exception;
use Ipuppet\Jade\Component\DatabaseDriver\PdoDatabaseDriver;
use Ipuppet\Jade\Component\Kernel\Config\ConfigLoader;
use Ipuppet\Jade\Component\Kernel\Kernel;
use Ipuppet\Jade\Component\Logger\Logger;
use Ipuppet\Jade\Foundation\Parameter\Parameter;
use Ipuppet\Jade\Foundation\Parameter\ParameterInterface;
use Ipuppet\Jade\Foundation\Path\Exception\PathException;
use Psr\Log\LoggerInterface;

abstract class Model
{
    /**
     * 数据库连接信息
     * @var ParameterInterface
     */
    protected ParameterInterface $database;
    /**
     * @var ?PdoDatabaseDriver
     */
    private ?PdoDatabaseDriver $pdo = null;
    /**
     * @var Kernel
     */
    private Kernel $kernel;
    /**
     * @var ConfigLoader
     */
    private ConfigLoader $configLoader;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ?DateTime
     */
    private ?DateTime $date = null;

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
    protected function init(): void
    {
        $this->kernel = new AppKernel();
        $this->configLoader = $this->kernel->getConfigLoader();
        $this->logger = new Logger();
        $this->logger->setName('PdoDatabaseDriver')
            ->setOutput($this->kernel->getLogPath());
        $this->database = new Parameter($this->configLoader
            ->setName('database')
            ->loadFromFile()
            ->toArray());
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
        return $this->configLoader
            ->setName($name)
            ->loadFromFile();
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
     * @return PdoDatabaseDriver
     */
    protected function getPdo(string $dbname = '', string $username = '', string $password = ''): PdoDatabaseDriver
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
            $this->pdo = new PdoDatabaseDriver($this->logger, $this->database->toArray());
        }
        return $this->pdo;
    }

    /**
     * 获取当前日期
     * @param string $format 日期格式
     * @return string
     * @throws Exception
     */
    protected function dateNow(string $format = 'Y-m-d H:i:s'): string
    {
        if ($this->date === null) {
            $this->date = new DateTime();
            $this->date->setTimezone(new DateTimeZone('PRC'));
        }
        return $this->date->format($format);
    }
}