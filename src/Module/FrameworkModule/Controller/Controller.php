<?php


namespace Zimings\Jade\Module\FrameworkModule\Controller;


use PDO;
use Zimings\Jade\Component\DatabaseDriver\PdoDatabaseDriver;
use Zimings\Jade\Component\Http\RequestFactory;
use Zimings\Jade\Component\Kernel\ConfigLoader\Exception\ConfigLoaderException;
use Zimings\Jade\Component\Kernel\Kernel;
use Zimings\Jade\Component\Logger\Logger;
use Zimings\Jade\Component\Router\Router;
use Zimings\Jade\Foundation\Path\Exception\PathException;

class Controller
{
    protected $router;
    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->router = new Router(RequestFactory::createFromSuperGlobals());
    }

    /**
     * @return PDO|PdoDatabaseDriver
     * @throws PathException
     * @throws ConfigLoaderException
     */
    protected function getPdo()
    {
        if ($this->pdo === null) {
            $configLoader = $this->kernel->getConfigLoader()
                ->setName('database')
                ->loadFromFile();
            $logger = new Logger();
            $logger->setName('PdoDatabaseDriver')
                ->setOutput($this->kernel->getLogDir());
            $this->pdo = new PdoDatabaseDriver($logger, $configLoader->all());
        }
        return $this->pdo;
    }
}