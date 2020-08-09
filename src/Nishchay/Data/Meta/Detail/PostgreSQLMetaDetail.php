<?php

namespace Nishchay\Data\Meta\Detail;

use Nishchay\Data\Query;

/**
 * Meta Detail class for PostgreSQL database.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class PostgreSQLMetaDetail implements MetaDetailInterface
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
     * Returns all child table with column name have foreign key to given table.
     * 
     * @param   string      $table
     * @return  array
     */
    public function getChildTables($table)
    {
        return $this->setConstraintTables()
                        ->query
                        ->addJoin([
                            $this->database . '.key_column_usage' => [
                                'constraint_schema' => 'constraint_schema',
                                'constraint_name' => 'constraint_name'
                            ]
                        ])
                        ->setColumn([
                            'columnName' => 'key_column_usage.column_name',
                            'childSchema' => 'constraint_column_usage.table_schema',
                            'childTable' => 'constraint_column_usage.table_name',
                            'childColumn' => 'constraint_column_usage.column_name'
                        ])->setCondition([
                    'constraint_column_usage.table_catalog' => $this->connectionDatabase,
                    'constraint_column_usage.table_name' => $table,
                    'table_constraints.constraint_type' => 'FOREIGN KEY'
                ])->get();
    }

    /**
     * Returns columns having foreign keys along with foreign key detail.
     * 
     * @param string $table
     * @return array
     */
    public function getForeignKeys($table, $column = null)
    {
        $this->setConstraintTables()
                ->query
                ->addJoin([
                    "{$this->database}.key_column_usage" => [
                        'constraint_schema' => 'constraint_schema',
                        'constraint_name' => 'constraint_name'
                    ]
                ])
                ->setColumn([
                    'columnName' => 'key_column_usage.column_name',
                    'targetSchema' => 'constraint_column_usage.constraint_schema',
                    'targetTable' => 'constraint_column_usage.table_name',
                    'targetColumn' => 'constraint_column_usage.column_name',
                    'constraintName' => 'key_column_usage.constraint_name'
                ])->setCondition([
            'table_constraints.table_catalog' => $this->connectionDatabase,
            'table_constraints.table_name' => $table,
            'table_constraints.constraint_type' => 'FOREIGN KEY'
        ]);

        if ($column !== null) {
            return $this->query
                            ->setCondition('key_column_usage.column_name', $column)
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
        $this->setConstraintTables();

        $this->query->setColumn([
                    'columnName' => 'constraint_column_usage.column_name',
                    'constraintName' => 'constraint_column_usage.constraint_name'
                ])
                ->setCondition([
                    'table_constraints.table_catalog' => $this->connectionDatabase,
                    'table_constraints.constraint_type' . Query::NOT_IN => ['PRIMARY KEY', 'FOREIGN KEY', 'CHECK'],
                    'table_constraints.table_name' => $table
        ]);

        if ($column !== null) {
            return $this->query
                            ->setCondition('constraint_column_usage.column_name', $column)
                            ->getOne();
        }

        return $this->query->get();
    }

    /**
     * Returns primary key of given table.
     * 
     * @param string $table
     * @return \stdClass
     */
    public function getPrimaryKey($table)
    {
        return $this->setConstraintTables()
                        ->prepareColumnQuery($table)
                        ->setColumn([
                            'constraintName' => 'table_constraints.constraint_name'
                        ])
                        ->setCondition([
                            'table_constraints.constraint_type' => 'PRIMARY KEY'
                        ])->getOne();
    }

    /**
     * Sets table constraints and its related join to fetch constraints on
     * Query instance.
     * 
     * @return $this
     */
    private function setConstraintTables()
    {
        $this->query->setTable($this->database . '.table_constraints')
                ->addJoin([
                    $this->database . '.constraint_column_usage' => [
                        'constraint_schema' => 'constraint_schema',
                        'constraint_name' => 'constraint_name'
                    ],
                    $this->database . '.columns' => [
                        'table_schema' => 'constraint_schema',
                        'table_catalog',
                        'table_name',
                        'column_name' => 'constraint_column_usage.column_name'
                    ]
        ]);
        return $this;
    }

    /**
     * Returns given column detail.
     * 
     * @param type $table
     * @param type $name
     * @param type $foreign
     * @return type
     */
    public function getTableColumn($table, $name, $foreign = true)
    {
        $this->query->setTable($this->database . '.columns');
        $row = $this->prepareColumnQuery($table)
                ->setCondition(['column_name' => $name])
                ->getOne();

        if ($row === false) {
            return false;
        }

        return $foreign ? current($this->setColumnKey([$row], $table)) : $row;
    }

    /**
     * Return all columns along with their detail,
     *  
     * @param string $table
     * @param boolean $foreign
     * @return array
     */
    public function getTableColumns($table, $foreign = true)
    {
        $this->query->setTable($this->database . '.columns');
        $columns = $this->prepareColumnQuery($table)->get();
        return $foreign ? $this->setColumnKey($columns, $table) : $columns;
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
                            . '.table_constraints')
                    ->addJoin([
                        $this->database . '.constraint_column_usage' => [
                            'constraint_schema' => 'constraint_schema',
                            'constraint_name' => 'constraint_name'
                        ]
                    ])
                    ->setColumn([
                        'keyName' => 'constraint_type'
                    ])
                    ->setCondition([
                        'table_constraints.table_catalog' => $this->connectionDatabase,
                        'table_constraints.table_name' => $table,
                        'constraint_column_usage.column_name' => $col->name
                    ])
                    ->get();

            $col->primaryKey = false;
            $col->uniqueKey = false;
            $col->foreignKey = false;
            foreach ($keys as $keyRow) {
                if ($keyRow->keyName === 'PRIMARY KEY') {
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
        return $this->query->setColumn([
                            'name' => 'columns.column_name',
                            'nullable' => 'columns.is_nullable',
                            'dataType' => 'columns.data_type',
                            'maxLength' => 'columns.character_maximum_length',
                            'precision' => 'columns.numeric_precision',
                            'scale' => 'columns.numeric_scale'
                        ])
                        ->setCondition([
                            'columns.table_catalog' => $this->connectionDatabase,
                            'columns.table_name' => $table]);
    }

    /**
     * Returns all of given database and belongs to public schema.
     * 
     * @return array
     */
    public function getTables()
    {
        return $this->query->setTable("{$this->database}.tables")
                        ->setCondition([
                            'table_catalog' => $this->connectionDatabase,
                            'table_type' => 'BASE TABLE'
                        ])
                        ->setColumn(['tableName' => 'table_name'])
                        ->get();
    }

    /**
     * Returns TRUE if given table name exist in public schema of catalog.
     * 
     * @param string $table
     * @return boolean
     */
    public function isTableExist($table)
    {
        $row = $this->query->setTable("{$this->database}.tables")
                        ->setCondition([
                            'table_catalog' => $this->connectionDatabase,
                            'table_name' => $table
                        ])->getOne();
        return $row !== false;
    }

}
