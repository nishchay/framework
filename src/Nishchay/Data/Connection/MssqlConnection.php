<?php

namespace Nishchay\Data\Connection;

use Nishchay\Data\Builder\MssqlBuilder;

/**
 * MSSQL connection class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class MssqlConnection extends MssqlBuilder
{

    /**
     * Connects to MSSQL database.
     * 
     * @param \stdClass $config
     * @param string $connectionName
     */
    public function __construct($config, $connectionName)
    {
        parent::__construct($connectionName);
        $this->setOffline($config);
        $this->databaseName = $config->dbname;
        $this->connect($this->getDsn($config), $config->username, $config->password, $config);
    }

    /**
     * 
     * @param   object  $config
     * @return  string
     */
    private function getDsn($config)
    {
        return 'sqlsrv:server=' . $config->host . ',' . $config->port . ';Database=' . $config->dbname;
    }

}
