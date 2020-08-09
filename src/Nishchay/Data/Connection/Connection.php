<?php

namespace Nishchay\Data\Connection;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\ApplicationException;
use Nishchay\Data\Connection\MysqlConnection;
use Nishchay\Data\Connection\PostgresqlConnection;

/**
 * Database connection class. 
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Connection
{

    /**
     * Property name which stores all connection
     * configuration.
     */
    const CONNECTIONS = 'connections';

    /**
     * Property name which store default connection name.
     * 
     */
    const DEFAULT_CONNECTION = 'defaultConnection';

    /**
     * Connection configurations.
     * 
     * @var \stdClass 
     */
    private static $connections = [];

    /**
     * Connected databases.
     * 
     * @var array 
     */
    private static $connected = [];

    /**
     * Default connection name.
     * 
     * @var string 
     */
    private static $defaultConnection;

    /**
     * 
     * @param   stirng      $name
     * @return  \Nishchay\Data\Connection\AbstractConnection
     */
    public static function connection($name)
    {
        # We are connecting database only when it's first usage is made.
        self::connect($name);
        return isset(self::$connected[$name]) ? self::$connected[$name] : false;
    }

    /**
     * Returns TRUE if connection configuration exist for given connection name
     * @param type $name
     * @return type
     */
    public static function isConnnectionExist($name)
    {
        return isset(self::$connections->{$name});
    }

    /**
     * Connects to datbase.
     * 
     * @param   string  $name
     * @return  NULL
     */
    private static function connect($name)
    {
        # Already connected to datbase, we don't need to reconnect.
        if (array_key_exists($name, self::$connected)) {
            return;
        }

        if (!isset(self::$connections->{$name})) {
            throw new ApplicationException('[' . $name . '] database'
                    . ' connection configuration does not exist.', null, null, 911001);
        }

        $config = self::$connections->{$name};

        switch ($config->type) {
            case 'mysql':
                $connection = new MysqlConnection($config, $name);
                break;
            case 'postgresql':
            case 'postgre':
                $connection = new PostgresqlConnection($config, $name);
                break;
            case 'mssql':
                $connection = new MssqlConnection($config, $name);
                break;
            default:
                throw new NotSupportedException($config->type . ' is not'
                        . ' supported for database.', null, null, 911002);
        }

        self::$connected[$name] = $connection;
    }

    /**
     * 
     * @return string
     */
    public static function getDefaultConnectionName()
    {
        return self::$defaultConnection;
    }

    /**
     * 
     * @param       string      $query
     * @param       array       $bind
     * @param       string      $connection
     * @return      array
     */
    public static function execute($query, $bind = [], $connection = null)
    {
        $connection = ($connection === null ? self::$defaultConnection : $connection);
        return self::connection($connection)->execute($query, $bind);
    }

}
