<?php


namespace Zimings\Jade\Module\FrameworkModule\Model;


use AppKernel;
use PDO;
use Psr\Log\LoggerInterface;
use Zimings\Jade\Component\DatabaseDriver\PdoDatabaseDriver;
use Zimings\Jade\Component\Kernel\ConfigLoader\ConfigLoader;
use Zimings\Jade\Component\Kernel\ConfigLoader\Exception\ConfigLoaderException;
use Zimings\Jade\Component\Kernel\ConfigLoader\JsonParser;
use Zimings\Jade\Component\Kernel\Kernel;
use Zimings\Jade\Component\Logger\Logger;
use Zimings\Jade\Foundation\Parameter;
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
     * @var Parameter
     */
    private $database;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Model constructor.
     * @throws PathException
     * @throws ConfigLoaderException
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @throws ConfigLoaderException
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
        $this->database = $this->configLoader
            ->setName('database')
            ->loadFromFile()
            ->all();
        $this->pdo = new PdoDatabaseDriver($this->logger, $this->database);
    }

    /**
     * @param $name
     * @return Parameter
     * @throws ConfigLoaderException
     */
    protected function getConfigByName($name): Parameter
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
     * @return PDO
     */
    protected function getPdo()
    {
        return $this->pdo;
    }
}