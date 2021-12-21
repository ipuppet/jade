<?php


namespace Ipuppet\Jade\Component\DatabaseDriver;

use Error;
use PDO;
use PDOStatement;
use PDOException;
use Psr\Log\LoggerInterface;

class PdoDatabaseDriver
{
    /**
     * @var PDO
     */
    public PDO $pdo;

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
            //database 默认值 mysql
            $config['database'] = $config['database'] ?? 'mysql';
            $this->pdo = new PDO(
                sprintf(
                    '%s:host=%s;dbname=%s;port=%s;charset=%s',
                    $config['database'],
                    $config['host'],
                    $config['dbname'],
                    $config['port'],
                    $config['charset']
                ),
                $config['username'],
                $config['password']
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //使数据类型对等
            $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException | Error $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function prepareAndQuery(string $sql, array $param, int $style = PDO::FETCH_ASSOC)
    {
        $query = $this->pdo->prepare($sql);
        $query->execute($param);
        return $query->fetchAll($style);
    }

    public function prepareAndExec(string $sql, array $param): int
    {
        $query = $this->pdo->prepare($sql);
        $query->execute($param);
        return $query->rowCount();
    }

    public function lastInsertId(?string $name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * @param string $sql
     * @return array
     */
    public function query(string $sql, int $style = PDO::FETCH_ASSOC): array
    {
        try {
            $rows = $this->pdo->query($sql);
            return $rows->fetchAll($style);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param string $sql
     * @return int
     */
    public function exec(string $sql): int
    {
        $line = 0;
        try {
            $line = (int)$this->pdo->exec($sql);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
        return $line;
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return $this->pdo->prepare($query, $options);
    }

    public function closeAutoCommit(): self
    {
        $this->pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
        return $this;
    }

    /**
     * 开始事件
     * 将会自动调用 closeAutoCommit
     * @return bool
     */
    public function beginTransaction(): bool
    {
        $this->closeAutoCommit();
        return $this->pdo->beginTransaction();
    }

    /**
     * 提交事件
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * 回滚事件
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->pdo->rollback();
    }
}
