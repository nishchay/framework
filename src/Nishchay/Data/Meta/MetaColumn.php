<?php

namespace Nishchay\Data\Meta;

use Nishchay\Data\Connection\Connection;

/**
 * Meta column class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class MetaColumn
{

    /**
     * Table name.
     * 
     * @var string 
     */
    private $table;

    /**
     * Column name.
     * 
     * @var string 
     */
    private $columnName;

    /**
     * MetaDetail instance for database.
     * 
     * @var Detail\MetaDetailInterface 
     */
    private $metaDatabase;

    /**
     * Column detail.
     * 
     * @var \stdClass
     */
    private $detail;

    /**
     * Detail of foreign key.
     * 
     * @var \stdClass
     */
    private $foreignKey;

    /**
     * 
     * @param   string      $table
     * @param   string      $column_name
     * @param   string      $connection
     */
    public function __construct($table, $column_name, $connection = NULL)
    {
        $this->table = $table;
        $this->columnName = $column_name;
        $connectionName = ($connection === NULL ? Connection::getDefaultConnectionName() : $connection);
        $this->metaDatabase = Connection::connection($connectionName)->getMetaDetailInstance();
        $this->detail = $this->getDetail();
    }

    /**
     * Returns detail of column.
     * 
     * @return type
     */
    private function getDetail()
    {
        return $this->metaDatabase->getTableColumn($this->table, $this->columnName);
    }

    /**
     * Returns data type of column.
     * 
     * @return string
     */
    public function getDataType()
    {
        return $this->detail->dataType;
    }

    /**
     * Returns max length of column.
     * 
     * @return int
     */
    public function getDataTypeLength()
    {
        return $this->detail->maxLength;
    }

    /**
     * Returns TRUE if column is primary key.
     * 
     * @return boolean
     */
    public function isPrimary()
    {
        return isset($this->detail->primaryKey);
    }

    /**
     * Returns TRUE if column is foreign key.
     * 
     * @return boolean
     */
    public function isForeign()
    {
        return isset($this->detail->foreignKey);
    }

    /**
     * Returns TRUE if column is unique key.
     * 
     * @return boolean
     */
    public function isUnique()
    {
        return isset($this->detail->uniqueKey);
    }

    /**
     * Returns TRUE if column supports NULL value.
     * 
     * @return boolean
     */
    public function isNullable()
    {
        return $this->detail->nullable === 'NO' ? true : false;
    }

    /**
     * Returns detail of foreign key if it exists otherwise it returns FALSE.
     * 
     * @return \stdClass|bool
     */
    private function getForeignKey()
    {
        if ($this->foreignKey === null) {
            return $this->foreignKey = $this->metaDatabase->getForeignKeys($this->table, $this->columnName);
        }

        return $this->foreignKey;
    }

    /**
     * Returns parent database name if the column is foreign.
     * 
     * @return string
     */
    public function getParentDatabaseName()
    {
        if (($foreignKey = $this->getForeignKey()) === false) {
            return null;
        }
        return $foreignKey->targetSchema;
    }

    /**
     * Returns parent table name if the column is foreign.
     * 
     * @return string
     */
    public function getParentTable()
    {
        if (($foreignKey = $this->getForeignKey()) === false) {
            return null;
        }
        return $foreignKey->targetTable;
    }

    /**
     * Returns parent column name if the column is foreign.
     * 
     * @return string
     */
    public function getParentTableColumnName()
    {
        if (($foreignKey = $this->getForeignKey()) === false) {
            return null;
        }
        return $foreignKey->targetColumn;
    }

    /**
     * Returns foreign key name if the column is foreign.
     * 
     * @return string
     */
    public function getForeignKeyName()
    {
        if (($foreignKey = $this->getForeignKey()) === false) {
            return null;
        }
        return $foreignKey->constraintName;
    }

}
