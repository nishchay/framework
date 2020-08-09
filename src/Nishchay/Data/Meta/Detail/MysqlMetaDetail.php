<?php

namespace Nishchay\Data\Meta\Detail;

use Nishchay\Data\Query;

/**
 * Meta Detail class for MySQL database.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class MysqlMetaDetail implements MetaDetailInterface
{

    /**
     * Query instance.
     * 
     * @var \Nishchay\Data\Query 
     */
    private $query;

    /**
     * Information schema  database name.
     * 
     * @var string 
     */
    private $database = 'information_schema';

    /**
     * Default database name of connection.
     * 
     * @var string 
     */
    private $connectionDatabase;

    /**
     * 
     * @param   string      $connectionName
     * @param   string      $connectionDatabase
     */
    public function __construct($connectionName, $connectionDatabase)
    {
        $this->connectionDatabase = $connectionDatabase;
        $this->query = new Query($connectionName);
    }

    /**
     * Returns all table from the database.
     * 
     * @return array
     */
    public function getTables()
    {
        return $this->query->setTable($this->database . '.TABLES')
                        ->setCondition([
                            'TABLE_SCHEMA' => $this->connectionDatabase,
                            'TABLE_TYPE' => 'BASE TABLE'
                        ])
                        ->setColumn(['tableName' => 'TABLE_NAME'])
                        ->get();
    }

    /**
     * Returns TRUE if the table exist otherwise false.
     * 
     * @param   string      $table
     * @return  boolean
     */
    public function isTableExist($table)
    {
        $row = $this->query->setTable($this->database . '.TABLES')
                        ->setCondition([
                            'TABLE_SCHEMA' => $this->connectionDatabase,
                            'TABLE_NAME' => $table
                        ])->getOne();
        return $row !== false;
    }

    /**
     * Returns all columns of given table.
     * 
     * @param string $table
     */
    public function getTableColumns($table, $foreign = true)
    {
        $columns = $this->prepareColumnQuery($table)->get();
        return $foreign ? $this->setColumnKey($columns, $table) : $columns;
    }

    /**
     * Returns table column detail.
     * Returns FALSE if the given column name not found in table.
     * 
     * @param   string              $table
     * @param   string              $name
     * @param   boolean             $foreign
     * @return  boolean|object
     */
    public function getTableColumn($table, $name, $foreign = true)
    {
        $row = $this->prepareColumnQuery($table)
                ->setCondition(['COLUMNS.COLUMN_NAME' => $name])
                ->getOne();

        # Simply return false when column not found.
        if ($row === false) {
            return false;
        }

        return $foreign ? current($this->setColumnKey([$row], $table)) : $row;
    }

    /**
     * 
     * @param   array   $columns
     * @param   string  $table
     * @return  array
     */
    private function setColumnKey($columns, $table)
    {
        foreach ($columns as $col) {
            $keys = $this->query->setTable($this->database
                            . '.KEY_COLUMN_USAGE')
                    ->setColumn([
                        'keyName' => 'CONSTRAINT_NAME'
                    ])
                    ->setCondition([
                        'TABLE_SCHEMA' => $this->connectionDatabase,
                        'TABLE_NAME' => $table,
                        'COLUMN_NAME' => $col->name
                    ])
                    ->get();

            $col->primaryKey = false;
            $col->uniqueKey = false;
            $col->foreignKey = false;
            foreach ($keys as $keyRow) {
                if ($keyRow->keyName === 'PRIMARY') {
                    $col->primaryKey = true;
                } else if ($keyRow->keyName === 'UNIQUE') {
                    $col->uniqueKey = true;
                } else {
                    $col->foreignKey = true;
                }
            }
        }
        return $columns;
    }

    /**
     * Prepares base query for fetching column.
     * 
     * @param   string                  $table
     * @return  \Nishchay\Data\Query
     */
    private function prepareColumnQuery($table)
    {
        return $this->query->setTable($this->database . '.COLUMNS')
                        ->setColumn([
                            'name' => 'COLUMN_NAME',
                            'nullable' => 'IS_NULLABLE',
                            'dataType' => 'DATA_TYPE',
                            'maxLength' => 'CHARACTER_MAXIMUM_LENGTH',
                            'precision' => 'NUMERIC_PRECISION',
                            'scale' => 'NUMERIC_SCALE',
                        ])
                        ->setCondition([
                            'COLUMNS.TABLE_SCHEMA' => $this->connectionDatabase,
                            'COLUMNS.TABLE_NAME' => $table]);
    }

    /**
     * Returns primary key of table.
     * 
     * @param type $table
     * @return type
     */
    public function getPrimaryKey($table)
    {
        return $this->prepareColumnQuery($table)
                        ->setCondition(['COLUMN_KEY' => 'PRI'])
                        ->getOne();
    }

    /**
     * Returns all foreign column of given table or column.
     * 
     * @param   string  $table
     * @return  array
     */
    public function getForeignKeys($table, $column = null)
    {
        $this->query->setTable($this->database . '.TABLE_CONSTRAINTS')
                ->setColumn([
                    'columnName' => 'KEY_COLUMN_USAGE.COLUMN_NAME',
                    'targetSchema' => 'KEY_COLUMN_USAGE.REFERENCED_TABLE_SCHEMA',
                    'targetTable' => 'KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME',
                    'targetColumn' => 'KEY_COLUMN_USAGE.REFERENCED_COLUMN_NAME',
                    'constraintName' => 'KEY_COLUMN_USAGE.CONSTRAINT_NAME'
                ])
                ->addJoin([Query::INNER_JOIN . $this->database .
                    '.KEY_COLUMN_USAGE' => 'CONSTRAINT_NAME'])
                ->setCondition([
                    'TABLE_CONSTRAINTS.TABLE_SCHEMA' => $this->connectionDatabase,
                    'TABLE_CONSTRAINTS.CONSTRAINT_TYPE' => 'FOREIGN KEY',
                    'TABLE_CONSTRAINTS.TABLE_NAME' => $table
        ]);

        if ($column !== null) {
            return $this->query
                            ->setCondition('KEY_COLUMN_USAGE.COLUMN_NAME', $column)
                            ->getOne();
        }

        return $this->query->get();
    }

    /**
     * Returns indexes defined on table or table column.
     * 
     * @param type $table
     * @param type $column
     * @return array
     */
    public function getIndexes($table, $column = null)
    {
        $this->query->setTable($this->database . '.TABLE_CONSTRAINTS')
                ->setColumn([
                    'columnName' => 'KEY_COLUMN_USAGE.COLUMN_NAME',
                    'constraintName' => 'KEY_COLUMN_USAGE.CONSTRAINT_NAME'
                ])
                ->addJoin([Query::INNER_JOIN . $this->database .
                    '.KEY_COLUMN_USAGE' => 'CONSTRAINT_NAME'])
                ->setCondition([
                    'TABLE_CONSTRAINTS.TABLE_SCHEMA' => $this->connectionDatabase,
                    'TABLE_CONSTRAINTS.CONSTRAINT_TYPE' . Query::NOT_IN => ['PRIMARY KEY', 'FOREIGN KEY'],
                    'TABLE_CONSTRAINTS.TABLE_NAME' => $table
        ]);

        if ($column !== null) {
            return $this->query
                            ->setCondition('KEY_COLUMN_USAGE.COLUMN_NAME', $column)
                            ->getOne();
        }

        return $this->query->get();
    }

    /**
     * Returns all child table with column name have foreign key to given table.
     * 
     * @param   string      $table
     * @return  array
     */
    public function getChildTables($table)
    {
        return $this->query->setTable($this->database . '.KEY_COLUMN_USAGE')
                        ->setColumn([
                            'columnName' => 'REFERENCED_COLUMN_NAME',
                            'childSchema' => 'TABLE_SCHEMA',
                            'childTable' => 'TABLE_NAME',
                            'childColumn' => 'COLUMN_NAME',
                        ])
                        ->setCondition([
                            'KEY_COLUMN_USAGE.REFERENCED_TABLE_SCHEMA' => $this->connectionDatabase,
                            'KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME' => $table,
                        ])
                        ->get();
    }

}
