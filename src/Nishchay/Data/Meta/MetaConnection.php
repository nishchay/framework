<?php

namespace Nishchay\Data\Meta;

use Nishchay\Data\Connection\Connection;

/**
 * Meta connection class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class MetaConnection
{

    /**
     * Instance of Meta connection class.
     * 
     * @var Detail\MetaDetailInterface 
     */
    private $metaDatabase;

    /**
     * 
     * @param string $connection
     */
    public function __construct($connection = null)
    {
        $connectionName = ($connection === null ? Connection::getDefaultConnectionName() : $connection);
        $this->metaDatabase = Connection::connection($connectionName)->getMetaDetailInstance();
    }

    /**
     * Returns all tables.
     * 
     * @return array
     */
    public function getTables()
    {
        return $this->metaDatabase->getTables();
    }

    /**
     * Returns TRUE if table exists.
     * 
     * @param string $table
     * @return bool
     */
    public function isTableExist($table)
    {
        return $this->metaDatabase->isTableExist($table);
    }

}
