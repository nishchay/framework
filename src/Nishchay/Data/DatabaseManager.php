<?php

namespace Nishchay\Data;

use Nishchay\Exception\ApplicationException;
use Nishchay\Data\Connection\Connection;
use Nishchay\Data\Meta\MetaConnection;
use Nishchay\Data\Meta\MetaTable;
use Exception;

/**
 * Database manager class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DatabaseManager
{

    /**
     * Connection name.
     * 
     * @var string 
     */
    private $connnectionName;

    /**
     *
     * @var type 
     */
    private $table;

    /**
     * Flag for table exist or not.
     * 
     * @var boolean
     */
    private $tableExist = NULL;

    /**
     * Column to be create or add.
     * 
     * @var array 
     */
    private $creationColumns = [];

    /**
     * Column to be remove.
     * 
     * @var array 
     */
    private $removalColumns = [];

    /**
     * Primary key name.
     * 
     * @var string 
     */
    private $primaryKey;

    /**
     * Unique keys.
     * 
     * @var array 
     */
    private $indexKey = [];

    /**
     * Foreign keys.
     * 
     * @var array 
     */
    private $foreignKey = [];

    /**
     * Key to be remove.
     * 
     * @var array 
     */
    private $removalKey = [];

    /**
     * 
     * @param   string      $connection
     */
    public function __construct($connection = NULL)
    {
        $this->connnectionName = ($connection === NULL ? Connection::getDefaultConnectionName() : $connection);
    }

    /**
     * Sets table name.
     * 
     * @param   string                                      $name
     * @return  \Nishchay\Data\DatabaseManager
     */
    public function setTableName($name)
    {
        $this->table = $name;
        return $this->setTableExist();
    }

    /**
     * Sets table_exist to TRUE if table exist or FALSE.
     * 
     * @return \Nishchay\Data\DatabaseManager
     */
    private function setTableExist()
    {
        $metaConnection = new MetaConnection($this->connnectionName);
        $this->tableExist = $metaConnection->isTableExist($this->table);
        return $this;
    }

    /**
     * Returns TRUE if table exist otherwise FALSE.
     * 
     * @return type
     */
    public function isTableExist()
    {
        return $this->tableExist;
    }

    /**
     * Sets column to be added or change in table.
     * If $name passes as array, it will be renamed from key to 
     * value of array.
     * Passing $default as 
     *  1. FALSE will make column NOT NULL.
     *  2. NULL will make column default NULL
     *  3. ^ prefix with string will not escape default string.
     * 
     * @param   string                  $name
     * @param   array                   $dataType
     * @param   mixed                   $default
     * @param   string                  $comment
     * @return  \Nishchay\Data\DatabaseManager
     */
    public function setColumn($name, $dataType = ['varchar', 50],
            $default = NULL, $comment = '')
    {
        $this->creationColumns[] = [
            'name' => $name,
            'type' => $dataType,
            'default' => $default,
            'comment' => $comment
        ];
        return $this;
    }

    /**
     * Removes column from table.
     * $name can be string or array.
     * 
     * @param   string|array    $name
     * @return  \Nishchay\Data\DatabaseManager
     */
    public function removeColumn($name)
    {
        foreach ((array) $name as $columnName) {
            $this->removalColumns[$columnName] = $columnName;
        }
        return $this;
    }

    /**
     * Sets primary of key of table on given column name.
     * 
     * @param    string                 $name
     * @return  \Nishchay\Data\DatabaseManager
     * @throws  ApplicationException
     */
    public function setPrimaryKey($name)
    {
        $this->primaryKey = $name;
        return $this;
    }

    /**
     * Adds foreign key to table.
     * $parent must be an array containing key table=TABLE_NAME & 
     * column=COLUMN_NAME. If want to reference column in different database or
     * schema, this method accepts database or schema name based on connection.
     * 
     * @param string $selfColumn
     * @param array $parentColumn
     * @param string $onUpdate
     * @param string $onDelete
     * @return \Nishchay\Data\DatabaseManager
     * @throws \Exception
     */
    public function addForeignKey($selfColumn, $parentColumn, $onUpdate = 'CASCADE',
            $onDelete = 'CASCADE')
    {
        if (!isset($parentColumn['table']) || !isset($parentColumn['column'])) {
            throw new ApplicationException('To add foreign key, second'
                    . ' argument must be array as [table=>TABLE,column=COLUMN].', null, null, 911063);
        }
        $this->foreignKey[] = [
            'self' => $selfColumn,
            'parent' => $parentColumn,
            'on_update' => strtoupper($onUpdate),
            'on_delete' => strtoupper($onDelete)
        ];
        return $this;
    }

    /**
     * Adds an index to table.
     * 
     * @param   string                  $column
     * @return  \Nishchay\Data\DatabaseManager
     * @throws  ApplicationException
     */
    public function addIndexKey($column, $type = 'UNIQUE')
    {
        $this->indexKey[] = [$column, $type];
        return $this;
    }

    /**
     * Removes constraints from table.
     * As constraint is removed by its name, here this method has flag $key
     * which gets constraint name for $name so that you do not have to find 
     * constraint name if $key = FALSE. 
     * If $key = TRUE, $name will be considered as constraint name
     * 
     * @param string $name
     * @param string $type
     * @return $this
     */
    public function removeConstraint($name, $type, $key = false)
    {
        $type = strtolower($type);
        if (!in_array($type, ['primary', 'index', 'foreign'])) {
            throw new Exception("Invalid constraint type[$type].");
        }

        if ($key === false && $this->tableExist) {
            $meta = new MetaTable($this->table, $this->connnectionName);
            if ($type === 'primary') {
                $primary = $meta->getPrimaryKey();
                if (isset($primary->constraintName)) {
                    $name = $primary->constraintName;
                }
            } else if ($type === 'foreign') {
                $primary = $meta->getForeignKeys($name);
                if (isset($primary->constraintName)) {
                    $name = $primary->constraintName;
                }
            } else if ($type === 'index') {
                $index = $meta->getIndexes($name);
                if (isset($index->constraintName)) {
                    $name = $index->constraintName;
                }
            }
        }

        $this->removalKey[] = [
            'key' => strtoupper($type),
            'name' => $name
        ];
        return $this;
    }

    /**
     * Resets property to their original value.
     * 
     */
    private function reset()
    {
        foreach (new static($this->connnectionName) as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Executes statement.
     * 
     * @return  mixed
     */
    public function execute()
    {
        $result = Connection::connection($this->connnectionName)
                ->executeDBManager($this);
        $this->reset();
        return $result;
    }

    /**
     * Returns data type as per database.
     * 
     * @param type $type
     * @param type $length
     * @return type
     */
    public function getType($type, $length)
    {
        return Connection::connection($this->connnectionName)->getType($type,
                        $length);
    }

    /**
     * Returns SQL statement.
     * 
     * @return type
     */
    public function getSQL()
    {
        return Connection::connection($this->connnectionName)->getDBSQL($this);
    }

}
