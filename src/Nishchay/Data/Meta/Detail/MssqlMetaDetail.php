<?php

namespace Nishchay\Data\Meta\Detail;

use Nishchay\Data\Query;

/**
 * Meta Detail class for MSSQL database.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class MssqlMetaDetail implements MetaDetailInterface
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
    private $database = 'INFORMATION_SCHEMA';

    /**
     * Default database name of connection.
     * 
     * @var string 
     */
    private $connectionDatabase;

    /**
     * Initialization.
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
     * Returns child tables of given table.
     * 
     * @param string $table
     * @return array
     */
    public function getChildTables($table)
    {
        # As INFORMATION_SCHEMA does not provides self column name, we
        # are using sys.
        $this->query->setTable('sys.foreign_key_columns');
        return $this->setSysConstraintColumnJoin()
                        ->setColumn([
                            'column_name' => 'refcolumn.name',
                            'child_schema' => 'selfschema.name',
                            'child_table' => 'selftable.name',
                            'child_column' => 'selfcolumn.name'
                        ])
                        ->setCondition('reftable.name', $table)
                        ->get();
    }

    /**
     * Returns all foreign keys of given table.
     * 
     * @param string $table
     * @return array
     */
    public function getForeignKeys($table, $column = null)
    {

        # As INFORMATION_SCHEMA does not provides self column name, here we
        # are using sys.
        $this->query->setTable('sys.foreign_key_columns');
        $this->setSysConstraintColumnJoin()
                ->setColumn([
                    'columnName' => 'selfcolumn.name',
                    'targetSchema' => 'refschema.name',
                    'targetTable' => 'reftable.name',
                    'targetColumn' => 'refcolumn.name',
                    'constraintName' => 'foreign_keys.name'
                ])
                ->setCondition('selftable.name', $table);

        if ($column !== null) {
            return $this->query->setCondition('selfcolumn.name', $column)
                            ->getOne();
        }

        return $this->query->get();
    }

    /**
     * Returns indexes defined on table or table column.
     * 
     * @param string $table
     * @param string $column
     * @return array
     */
    public function getIndexes($table, $column = null)
    {
        $this->query->setTable('sys.indexes')
                ->addJoin([
                    'sys.index_columns' => [
                        'object_id',
                        'index_id'
                    ],
                    'sys.columns' => [
                        'object_id' => 'index_columns.object_id',
                        'column_id' => 'index_columns.column_id'
                    ],
                    'sys.tables' => [
                        'object_id'
                    ]
                ])
                ->setColumn([
                    'constraintName' => 'indexes.name',
                    'columnName' => 'columns.name'
                ])
                ->setCondition([
                    'tables.name' => $table,
                    'indexes.is_primary_key' => 0,
                    'indexes.is_unique_constraint' => 0,
                    'indexes.is_disabled' => 0,
                    'indexes.is_hypothetical' => 0
        ]);

        if ($column !== null) {
            return $this->query->setCondition('columns.name', $column)
                            ->getOne();
        }

        return $this->query->get();
    }

    /**
     * Sets join to select column name of self table and referenced table.
     * 
     * @return \Nishchay\Data\Query
     */
    private function setSysConstraintColumnJoin()
    {
        $this->query->addJoin([
            Query::INNER_JOIN . 'sys.foreign_keys' => [
                'object_id' => 'constraint_object_id'
            ],
            # Self schema, table and column.
            Query::INNER_JOIN . 'sys.tables(selftable)' => [
                'object_id' => 'parent_object_id'
            ],
            Query::INNER_JOIN . 'sys.schemas(selfschema)' => [
                'schema_id' => 'selftable.schema_id'
            ],
            Query::INNER_JOIN . 'sys.columns(selfcolumn)' => [
                'object_id' => 'parent_object_id',
                'column_id' => 'parent_column_id'
            ],
            # Referenced schema, table and column,
            Query::INNER_JOIN . 'sys.tables(reftable)' => [
                'object_id' => 'referenced_object_id'
            ],
            Query::INNER_JOIN . 'sys.schemas(refschema)' => [
                'schema_id' => 'reftable.schema_id'
            ],
            Query::INNER_JOIN . 'sys.columns(refcolumn)' => [
                'object_id' => 'referenced_object_id',
                'column_id' => 'referenced_column_id'
            ],
        ]);
        return $this->query;
    }

    /**
     * Returns detail of primary key(That's column detail) of given table.
     * 
     * @param string $table
     * @return \stdClass
     */
    public function getPrimaryKey($table)
    {
        $this->query->setTable($this->database . '.TABLE_CONSTRAINTS')
                ->addJoin([
                    Query::INNER_JOIN . "{$this->database}.CONSTRAINT_COLUMN_USAGE" => [
                        'CONSTRAINT_NAME', 'TABLE_NAME'
                    ],
                    Query::INNER_JOIN . "{$this->database}.COLUMNS" => [
                        'TABLE_CATALOG',
                        'TABLE_NAME',
                        'COLUMN_NAME' => 'CONSTRAINT_COLUMN_USAGE.COLUMN_NAME'
                    ]
        ]);
        return $this->prepareColumnQuery($table)
                        ->setColumn([
                            'constraintName' => 'TABLE_CONSTRAINTS.CONSTRAINT_NAME'
                        ])
                        ->setCondition([
                            'TABLE_CONSTRAINTS.CONSTRAINT_TYPE' => 'PRIMARY KEY'
                        ])
                        ->getOne();
    }

    /**
     * Returns column detail of given table.
     * 
     * @param string $table
     * @param string $name
     * @param boolean $foreign
     * @return \stdClass
     */
    public function getTableColumn($table, $name, $foreign = true)
    {

        $this->query->setTable($this->database . '.COLUMNS');
        $row = $this->prepareColumnQuery($table)
                ->setCondition(['COLUMN_NAME' => $name])
                ->getOne();

        if ($row === false) {
            return false;
        }

        return $foreign ? current($this->setColumnKey([$row], $table)) : $row;
    }

    /**
     * Returns columns of given table.
     * 
     * @param string $table
     * @param boolean $foreign
     * @return array
     */
    public function getTableColumns($table, $foreign = true)
    {
        $this->query->setTable($this->database . '.COLUMNS');
        $columns = $this->prepareColumnQuery($table)->get();
        return $foreign ? $this->setColumnKey($columns, $table) : $columns;
    }

    /**
     * Returns name of constraint which is behind column's default value.
     * 
     * @param string $table
     * @param string $name
     * @return string
     */
    public function getDefaultConstraintName($table, $name)
    {
        $row = $this->query->setTable('sys.default_constraints')
                ->addJoin([
                    'sys.tables' => ['object_id' => 'parent_object_id'],
                    'sys.schemas' => 'schema_id',
                    'sys.columns' => [
                        'object_id' => 'parent_object_id',
                        'column_id' => 'parent_column_id'
                    ]
                ])
                ->setColumn('default_constraints.name')
                ->setCondition([
                    'tables.name' => $table,
                    'columns.name' => $name
                ])
                ->getOne();

        return isset($row->name) ? $row->name : false;
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

            $keys = $this->query->setTable($this->database . '.TABLE_CONSTRAINTS')
                    ->addJoin([
                        Query::INNER_JOIN . "{$this->database}.CONSTRAINT_COLUMN_USAGE" => [
                            'CONSTRAINT_NAME', 'TABLE_NAME'
                        ],
                        Query::INNER_JOIN . "{$this->database}.COLUMNS" => [
                            'TABLE_CATALOG',
                            'TABLE_NAME',
                            'COLUMN_NAME' => 'CONSTRAINT_COLUMN_USAGE.COLUMN_NAME'
                        ]
                    ])
                    ->setColumn([
                        'constraintType' => 'CONSTRAINT_TYPE'
                    ])
                    ->setCondition([
                        'TABLE_CONSTRAINTS.TABLE_CATALOG' => $this->connectionDatabase,
                        'CONSTRAINT_COLUMN_USAGE.TABLE_NAME' => $table,
                        'CONSTRAINT_COLUMN_USAGE.COLUMN_NAME' => $col->name
                    ])
                    ->get();
            $col->primaryKey = false;
            $col->uniqueKey = false;
            $col->foreignKey = false;
            foreach ($keys as $keyRow) {
                if ($keyRow->constraintType === 'PRIMARY KEY') {
                    $col->primaryKey = true;
                } else if ($keyRow->constraintType === 'UNIQUE') {
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
                            'name' => 'COLUMNS.COLUMN_NAME',
                            'nullable' => 'COLUMNS.IS_NULLABLE',
                            'dataType' => 'COLUMNS.DATA_TYPE',
                            'maxLength' => 'COLUMNS.CHARACTER_MAXIMUM_LENGTH',
                            'precision' => 'COLUMNS.NUMERIC_PRECISION',
                            'scale' => 'COLUMNS.NUMERIC_SCALE'
                        ])
                        ->setCondition([
                            'COLUMNS.TABLE_CATALOG' => $this->connectionDatabase,
                            'COLUMNS.TABLE_NAME' => $table]);
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
                            'TABLE_CATALOG' => $this->connectionDatabase,
                            'TABLE_TYPE' => 'BASE TABLE'
                        ])
                        ->setColumn(['table_name' => 'TABLE_NAME'])
                        ->get();
    }

    /**
     * Returns TRUE if given table exist in any of schema.
     * 
     * @param string $table
     * @return boolean
     */
    public function isTableExist($table)
    {
        $row = $this->query->setTable($this->database . '.TABLES')
                ->setCondition([
                    'TABLE_CATALOG' => $this->connectionDatabase,
                    'TABLE_NAME' => $table
                ])
                ->getOne();
        return $row !== false;
    }

}
