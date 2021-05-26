<?php

namespace Nishchay\Data\Connection;

use Nishchay;
use PDOException;
use Nishchay\Exception\ApplicationException;
use PDO;
use PDOStatement;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * Abstract Connection class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractConnection
{

    use MethodInvokerTrait;

    /**
     * Connection name.
     * 
     * @var string 
     */
    protected $connectionName;

    /**
     * Database name.
     * 
     * @var string 
     */
    protected $databaseName;

    /**
     * Database PDO instance.
     * 
     * @var PDO 
     */
    protected $pdo;

    /**
     * Database Meta Data Detail instance.
     * 
     * @var object 
     */
    protected $metaInstance;

    /**
     * Flag for connection is offline or not.
     * 
     * @var boolean
     */
    protected $offline = false;

    /**
     * 
     * @param type $connectionName
     */
    public function __construct($connectionName)
    {
        $this->connectionName = $connectionName;
    }

    /**
     * Sets connection offline flag based on config.
     * 
     * @param \stdClass $config
     */
    protected function setOffline($config)
    {
        $this->offline = isset($config->offline) ? ((bool) $config->offline) : false;
    }

    /**
     * 
     * @param   string      $dsn
     * @param   string      $username
     * @param   string      $password
     * @param   \stdClass       $config
     */
    protected function connect($dsn, $username, $password, $config)
    {
        # Will not connect to database when database
        # connection need to be offline.
        if ($this->offline) {
            return false;
        }

        $options = isset($config->options) ? $config->options : [
            PDO::ATTR_PERSISTENT => isset($config->persist) ? ((bool) $config->persist) : false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $ex) {
            throw new ApplicationException('Unable to connect to database.'
                    . ' Exception message is: '
                    . $ex->getMessage(), null, null, 911048);
        }
    }

    /**
     * Returns name of method to be call after execution of statement.
     * 
     * @param   array       $array
     * @return  string
     */
    protected function getAfterExecutionMethodName($array)
    {
        return 'return' . ucfirst(strtolower(current($array))) . 'Result';
    }

    /**
     * Returns select statement result.
     * 
     * @param   PDOStatement        $statement
     * @param   int                 $fetch
     * @return  array
     */
    protected function returnSelectResult(PDOStatement $statement): PDOStatement
    {
        return $statement;
    }

    /**
     * Returns last inserted ID of the insert statement.
     * 
     * @return int
     */
    protected function returnInsertResult(PDOStatement $statement): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Returns affected row count.
     * 
     * @param   PDOStatement        $statement
     * @return  int
     */
    protected function returnUpdateResult(PDOStatement $statement): int
    {
        return (int) $statement->rowCount();
    }

    /**
     * Returns number of deleted rows.
     * 
     * @param PDOStatement $statement
     * @return int
     */
    protected function returnDeleteResult(PDOStatement $statement): int
    {
        return (int) $statement->rowCount();
    }

    /**
     * Returns cache config name to be used for caching.
     * If there is no caching config for database connection then it returns null.
     * 
     * @return string
     */
    public function getCacheName()
    {
        $connectionName = $this->connectionName;

        $cacheConfig = Nishchay::getSetting('cache.database');

        $cacheName = null;

        # Looking for if there's cache name set for this database connection.
        if (isset($cacheConfig->connection) && isset($cacheConfig->connection->{$connectionName})) {
            $cacheName = $cacheConfig->connection->{$connectionName};
        }

        # If this is set to false, we will not use cache.
        if ($cacheName === false) {
            return null;
        }

        # If its null, means it need to be used as defined in database cache
        # default config.
        if ($cacheName === null) {
            
            # Database connection default cache is null means not to use cache.
            if (!isset($cacheConfig->default) || $cacheConfig->default === null) {
                return null;
            }

            $cacheName = $cacheConfig->default;
        }

        return $cacheName;
    }

    /**
     * Prepares query and then executes it.
     * 
     * @param   string      $query
     * @param   array       $bind
     * @return  mixed
     */
    public function execute($query, $bind = [])
    {
        if (empty($query)) {
            return false;
        }

        if ($this->offline) {
            throw new ApplicationException('Database connection [' .
                    $this->connectionName . '] is offline.', null, null, 911049);
        }

        var_dump($query);
        $statement = $this->pdo->prepare($query);
        $statement->execute($bind);
        $method = $this->getAfterExecutionMethodName(explode(' ', $query));
        if ($this->isCallbackExist([$this, $method])) {
            return $this->invokeMethod([$this, $method], [$statement]);
        }

        return true;
    }

    /**
     * Returns default database name.
     * 
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

}
