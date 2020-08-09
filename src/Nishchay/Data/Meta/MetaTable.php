<?php

namespace Nishchay\Data\Meta;

use Nishchay\Data\Connection\Connection;

/**
 * Meta table class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class MetaTable
{

    /**
     * Table name.
     * 
     * @var string 
     */
    private $table;

    /**
     * Meta database object.
     * 
     * @var Detail\MetaDetailInterface 
     */
    private $metaDatabase;

    /**
     * Flag for returning key with column or not.
     * 
     * @var boolean 
     */
    private $keyDetail = true;

    /**
     * 
     * @param   string      $table
     * @param   string      $connection
     */
    public function __construct($table, $connection = null)
    {
        $this->table = $table;
        $connectionName = ($connection === null ? Connection::getDefaultConnectionName() : $connection);
        $this->metaDatabase = Connection::connection($connectionName)->getMetaDetailInstance();
    }

    /**
     * Returns TRUE if the given column exist in table.
     * 
     * @param   string      $name
     * @return  boolean
     */
    public function isColumnExist($name)
    {
        return $this->getColumm($name) === false ? false : true;
    }

    /**
     * Returns all columns of table.
     * 
     * @return array
     */
    public function getColumns()
    {
        return $this->metaDatabase->getTableColumns($this->table, $this->keyDetail);
    }

    /**
     * Returns given column detail. Returns FALSE if column does not exist.
     * 
     * @param string $name
     * @return \stdClass|boolean
     */
    public function getColumm($name)
    {
        return $this->metaDatabase->getTableColumn($this->table, $name, $this->keyDetail);
    }

    /**
     * Returns all indexes of table if no parameter passed otherwise it returns
     * index of passed column name.
     * 
     * @param string $name
     * @return array
     */
    public function getIndexes($name = null)
    {
        return $this->metaDatabase->getIndexes($this->table, $name);
    }

    /**
     * Returns all foreign keys of table.
     * 
     * @return array
     */
    public function getForeignKeys($column = null)
    {
        return $this->metaDatabase->getForeignKeys($this->table, $column);
    }

    /**
     * Returns primary key of table.
     * 
     * @return \stdClass|boolean
     */
    public function getPrimaryKey()
    {
        return $this->metaDatabase->getPrimaryKey($this->table);
    }

    /**
     * Returns all child table of given table.
     * 
     * @return array
     */
    public function getChildTables()
    {
        return $this->metaDatabase->getChildTables($this->table);
    }

    /**
     * Enable or disable whether to fetch key detail along with column.
     * 
     * @param boolean $flag
     */
    public function enableKeyDetail(bool $flag)
    {
        $this->keyDetail = $flag;
    }

}
