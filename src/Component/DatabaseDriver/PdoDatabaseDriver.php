<?php


namespace Ipuppet\Jade\Component\DatabaseDriver;


use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class PdoDatabaseDriver
{
    /**
     * @var PDO
     */
    private PDO $pdo;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * PdoDatabaseDriver constructor.
     * @param LoggerInterface $logger
     * @param array $config
     */
    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        try {
            //database默认值mysql
            $config['database'] = isset($config['database']) ? $config['database'] : 'mysql';
            $this->pdo = new PDO(
                sprintf(
                    '%s:host=%s;dbname=%s;port=%s;charset=%s',
                    $config['database'],
                    $config['host'],
                    $config['dbname'],
                    $config['port'],
                    $config['charset']
                ), $config['username'], $config['password']
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //使数据类型对等
            $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param string $sql
     * @return array
     */
    public function query(string $sql): array
    {
        $result = [];
        try {
            $rows = $this->pdo->query($sql);
            foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $result[] = $row;
            }
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
        return $result;
    }

    /**
     * @param string $sql
     * @return false|int
     */
    public function exec(string $sql)
    {
        $line = 0;
        try {
            $line = $this->pdo->exec($sql);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
        return $line;
    }

    function closeAutoCommit(): self
    {
        $this->pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
        return $this;
    }

    function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    function commit(): bool
    {
        return $this->pdo->commit();
    }

    function rollback(): bool
    {
        return $this->pdo->rollback();
    }
}