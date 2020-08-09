<?php

namespace Nishchay\Data\Connection;

use Nishchay\Data\Builder\PostgresqlBuilder;

/**
 * Description of PostgressConnection
 *
 * @author Pratik
 */
class PostgresqlConnection extends PostgresqlBuilder
{

    public function __construct($config, $connectionName)
    {
        parent::__construct($connectionName);
        $this->setOffline($config);
        $this->databaseName = $config->dbname;
        $this->connect($this->getDsn($config), $config->username, $config->password, $config);
    }

    private function getDsn($config)
    {
        return 'pgsql:host=' . $config->host . ';port=' . $config->port . ';dbname=' . $config->dbname;
    }

}
