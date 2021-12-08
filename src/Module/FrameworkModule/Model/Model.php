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
use Ipuppet\Jade\Foundation\Path\PathInterface;
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
    protected ?PdoDatabaseDriver $pdo = null;
    /**
     * @var Kernel
     */
    protected Kernel $kernel;
    /**
     * @var ConfigLoader
     */
    private ConfigLoader $configLoader;
    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var ?DateTime
     */
    private ?DateTime $date = null;

    private PathInterface $cachePath;

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
        $this->cachePath = $this->kernel->getCachePath();
        $this->storagePath = $this->kernel->getStoragePath();
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

    protected function cacheExists(string $name)
    {
        $path = $this->cachePath->setFile($name . '.cache');
        return file_exists($path);
    }

    /**
     * 返回解析后的内容
     * 返回内容以及缓存创建时的信息，包括有效期、创建时间等
     * 结构如下
     * [
     *     'createdTime' => (int),
     *     'life' => (int),
     *     'autoDelete' => (int),
     *     'content' => (string)
     * ]
     * @param string $name
     * @return array
     */
    protected function getCacheContent(string $name): array
    {
        $path = $this->cachePath->setFile($name . '.cache');
        if (!$this->cacheExists($name)) {
            return [];
        }
        $data = file_get_contents($path);
        $posAt = strpos($data, '@');
        $lifeInfo = explode('.', substr($data, 0, $posAt));
        return [
            'createdTime' => (int)$lifeInfo[0],
            'life' => (int)$lifeInfo[1],
            'autoDelete' => (int)$lifeInfo[2],
            'content' => substr($data, $posAt + 1)
        ];
    }

    /**
     * 获取缓存
     * 判断是否过期后决定是否内容，过期后返回 $default
     * @param string $name
     * @param [type] $default
     * @return void
     */
    protected function getCache(string $name, $default = null)
    {
        $path = $this->cachePath->setFile($name . '.cache');
        $cache = $this->getCacheContent($name);
        if (empty($cache)) return $default;
        if (($cache['createdTime'] + $cache['life']) < time()) {
            if ($cache['autoDelete']) unlink($path);
            return $default;
        }
        $data = $cache['content'];
        $json_arr = json_decode($data, 1); // 解析为数组
        if (json_last_error() === JSON_ERROR_NONE) $data = $json_arr;
        return $data;
    }

    protected function setCache(string $name, $data, int $life_sec = 300, $del = true): void
    {
        $path = $this->cachePath->setFile($name . '.cache');
        if (is_array($data)) $data = json_encode($data);
        if (!is_string($data)) $data = (string)$data;
        $delFlag = $del ? '1' : '0';
        $data = time() . ".$life_sec.$delFlag@" . $data;
        file_put_contents($path, $data);
    }

    protected function getStorageContent(string $name, string $default = ''): string
    {
        $path = $this->storagePath->setFile($name);
        return file_get_contents($path) ?? $default;
    }

    protected function setStorageContent(string $name, $content): void
    {
        $path = $this->cachePath->setFile($name);
        file_put_contents($path, $content);
    }
}
