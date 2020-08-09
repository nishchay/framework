<?php

namespace Nishchay\Data\Builder;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Data\Meta\Detail\MssqlMetaDetail;
use Nishchay\Data\DatabaseManager;

/**
 * Builder class for SQL Server(MS SQL).
 *  
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class MssqlBuilder extends AbstractBuilder
{

    /**
     * Statements to be executed before executing main statement.
     * 
     * @var array
     */
    private $beforeExecution = [];

    /**
     * Statements to be executed after executing main statement.
     * 
     * @var array 
     */
    private $afterExecution = [];

    /**
     * Supported type of index.
     * 
     * @var array 
     */
    private $index = [
        'index' => '',
        'unique' => 'UNIQUE',
        'clustered' => 'CLUSTERED',
        'xml' => 'PRIMARY XML',
        'columnstore' => 'COLUMNSTORE',
        'clustered columnstore' => 'CLUSTERED COLUMNSTORE'
    ];

    /**
     * Index name prefixes.
     * 
     * @var array 
     */
    private $indexPrefix = [
        'unique' => 'UNQ',
        'index' => 'IDX',
        'clustered' => 'CLST',
        'xml' => 'PMX',
        'columnstore' => 'CLMN',
        'clustered columnstore' => 'CLSTCLMN'
    ];

    /**
     * Init.
     * 
     * @param string $connectionName
     */
    public function __construct($connectionName)
    {
        parent::__construct($connectionName);
    }

    /**
     * Prepares query statement.
     * 
     * @return  string
     */
    protected function prepare()
    {
        $method = 'prepare' . ucfirst($this->getDetail('mode'));
        return call_user_func([$this, $method]);
    }

    /**
     * 
     * @return type
     */
    protected function prepareDBManagerStatement()
    {
        # Before creating statement, let's  find wheather table already
        # exist or not. setting mode to alter or create helps us making
        # 'create' or 'alter' statement.
        $mode = $this->getDBMode();
        $alterMode = $this->isDBAlterMode();

        # Processing each config detail to prepare statement.
        $this->processDBConfig($mode, $alterMode);

        # This is CREATE OR ALTER TABLE {NAME} statement.
        $statement = strtoupper($mode) . ' TABLE ' .
                $this->quote($this->getDBTable()) . ' ';



        # Create mode requires small brace enclosing to statement.
        if ($alterMode === false) {
            $dbJoinedStatement = $this->getDBJoinedStatement();

            # There's no change in columns so no query executed. In this case
            # checking if there's any removal.
            if (empty($dbJoinedStatement)) {
                $statement = 'ALTER TABLE ' . $this->quote($this->getDBTable()) . ' ';
                $this->processDBConfig('alter', true);
            } else {
                return $statement .= '(' . PHP_EOL .
                        $dbJoinedStatement . ')';
            }
        }

        $this->prefixDBStatement($statement);
        return false;
    }

    /**
     * Prefix database manager statement with alter table.
     * 
     * @param string $prefix
     * @return boolean
     */
    private function prefixDBStatement($prefix)
    {
        array_walk($this->dbManagerStatement, function (&$value) use ($prefix) {
            $value = $prefix . $value;
        });

        # In create table statement prefix is not required. This is being used
        # in prepareDBManagerStatement method it returns string if table is
        # created. Returning FALSE to denote that there will be multiple
        # statement to be executed.
        return $this->getDBJoinedStatement();
    }

    /**
     * Processes table creation columns.
     * 
     * @param   array       $columns
     * @param   boolean     $alter
     */
    protected function processDBCreationColumns($columns, $alter)
    {
        foreach ($columns as $config) {

            $type = $config['type'];
            if (is_array($type)) {
                $length = current($type);
                if (is_array($length)) {
                    $length = implode(',', $length);
                }

                $columnType = key($type) . '(' . $length . ') ';
            } else {
                $columnType = $type . ' ';
            }

            # We can not find wheather to add or change column but data type of 
            # $name helps us decide what we should do.$name is string means we 
            # need to add column otherwise alter column.
            $addColumn = TRUE;
            $name = $config['name'];
            if (is_array($name)) {
                $addColumn = FALSE;
                $this->prepareColumnRename(key($name), current($name));
                $name = current($name);
            }

            $column = $this->quote($name);

            # Column can be set to NOT NULL by specifying $default=FALSE.
            $default = $config['default'];
            if ($default === false || $default === null) {
                $default = ($default === NULL ? 'DEFAULT NULL' : 'NOT NULL');
            } else if ($default !== true) {
                # Default value can be used as it is if its being prefixed by
                # ^(caret).
                $default = 'DEFAULT ' . (strpos($default, '^') === 0 ?
                        substr($default, 1) : "'$default'");
            }

            # This is to add default to existing column. If the column is being
            # altered we have to create seperate statement for setting default
            # value of column.
            if ($alter && $addColumn === FALSE && $default !== TRUE) {
                $default = $this->prepareColumnSetDefault($name, $default);
            }

            if ($default === true) {
                $default = '';
            }

            # Creating ADD or ALTER COLUMN statement.
            $this->dbManagerStatement[] = ($alter === TRUE ?
                    ($addColumn ? 'ADD' : 'ALTER COLUMN') : '') .
                    " $column {$columnType} {$default}";
        }
    }

    /**
     * 
     * @param type $name
     * @param type $default
     */
    private function prepareColumnSetDefault($name, $default)
    {

        # There's constraint behind column's default value. Before setting
        # new value, we must drop existing constraint for default value.
        # Here using Meta Detail instnace we will fetch constraint name for 
        # same and then we will prepare drop constraint statement.
        $constraint = $this->getMetaDetailInstance()
                ->getDefaultConstraintName($this->getDBTable(), $name);
        # Creating drop constraint statement.
        if ($constraint !== FALSE) {
            $this->beforeExecution[] = 'ALTER TABLE ' .
                    $this->quote($this->getDBTable()) .
                    ' DROP CONSTRAINT IF EXISTS ' . $this->quote($constraint);
        }

        # Creating statement for setting default value of column.
        # If setting column to NOT NULL then it will go in same line, no need
        # for seperate line.
        if ($default !== 'NOT NULL') {
            $this->afterExecution[] = 'ALTER TABLE ' .
                    $this->quote($this->getDBTable()) . " ADD "
                    . " {$default} FOR " . $this->quote($name);
            $default = '';
        }
        return $default;
    }

    /**
     * Prepares rename column statement.
     * 
     * @param string $current
     * @param string $new
     */
    private function prepareColumnRename($current, $new)
    {
        if ($current !== $new) {
            $this->beforeExecution[] = "sp_rename  '" .
                    $this->getDBTable() . '.' . $current . "','{$new}',"
                    . "'COLUMN'";
        }
    }

    /**
     * Processes table primary key.
     * 
     * @param   string      $column
     * @param   boolean     $alter
     * @return  NULL
     */
    protected function processDBPrimaryKey($column, $alter)
    {
        if ($column === NULL) {
            return;
        }

        $this->dbManagerStatement[] = ($alter ? 'ADD ' : '') . 'CONSTRAINT ' .
                ('pk_' . $this->getDBTable() . '_' . $column)
                . ' PRIMARY KEY (' . $this->quote($column) . ')';
    }

    /**
     * Processes table foreign keys.
     * 
     * @param   array       $keys
     * @param   boolean     $alter
     */
    protected function processDBForeignKey($keys, $alter)
    {

        foreach ($keys as $config) {

            $statement = ($alter ? 'ADD ' : '');

            # Based on config we are preparing unique key name
            # at database level.
            $parent = $config['parent'];
            $statement .= "CONSTRAINT fk_" . $this->getDBTable() .
                    "_{$parent['table']}_{$parent['column']} ";

            # Self column name.
            $self = $config['self'];
            $statement .= 'FOREIGN KEY (' . $this->quote($self) . ') ';

            # Now we are preparing reference caluse which point to parent
            # table column. It also possible that self table may need
            # foreign key to another schema, in that case schema name can
            # come in config.
            $schema = isset($parent['schema']) ?
                    ($this->quote($parent['schema']) . '.') : '';
            $statement .= 'REFERENCES ' . $schema .
                    $this->quote($parent['table'])
                    . '(' . $this->quote($parent['column']) . ') ';

            # Cascading rule.
            $statement .= "ON DELETE {$config['on_delete']} ON UPDATE {$config['on_update']}";
            $this->dbManagerStatement[] = $statement;
        }
    }

    /**
     * Processes table removal key.
     * 
     * @param array $keys
     */
    protected function processDBRemovalKey($keys)
    {
        foreach ($keys as $config) {
            $name = $config['name'];
            if ($config['key'] === 'INDEX') {
                $this->beforeExecution[] = 'DROP INDEX IF EXISTS ' . $this->quote($name)
                        . ' ON ' . $this->quote($this->getDBTable());
                continue;
            }
            $this->dbManagerStatement[] = 'DROP CONSTRAINT IF EXISTS ' . $this->quote($name);
        }
    }

    /**
     * Executes query generated from Database Manager.
     * 
     * @param   \Nishchay\Data\DatabaseManager      $dbManager
     * @return  mixed
     */
    public function executeDBManager(DatabaseManager $dbManager)
    {
        $this->dbManagerStatement = $this->beforeExecution = $this->afterExecution = [];
        $this->setDBManagerData($dbManager);

        # We will have three type of statemnts. Main statement which can
        # contain multiple definitions & Statement to be exuted before and
        # after this main statement.
        $mainStatement = $this->prepareDBManagerStatement();

        # Executing statement to be executed before main statement.
        foreach ($this->beforeExecution as $statement) {
            $this->execute($statement);
        }

        # Executing main statement.
        if ($mainStatement === false) {

            # For altering table, there will be more than one statement. So we
            # here executing each statement one by one.
            foreach ($this->dbManagerStatement as $statement) {
                $this->execute($statement);
            }
        } else {
            $this->execute($mainStatement);
        }

        # Executing statement to be executed after main statement.
        foreach ($this->afterExecution as $statement) {
            $this->execute($statement);
        }

        # If we had any of statement executed, we will return TRUE.
        return !empty($this->dbManagerStatement) ||
                !empty($this->beforeExecution) ||
                !empty($this->afterExecution);
    }

    /**
     * Quotes column name.
     * 
     * @param string $name
     * @return string
     */
    protected function quoteColumn($name)
    {
        return '"' . $name . '"';
    }

    /**
     * Processes limit config to prepare LIMIT part of statement.
     * 
     * @param   string      $limit
     * @return  string
     */
    protected function processLimit($limit)
    {
        if (empty($limit)) {
            return '';
        }
        $limit = explode(',', $limit);
        $limit[1] = array_key_exists(1, $limit) ? $limit[1] : 0;
        return PHP_EOL . "OFFSET {$limit[1]} ROWS"
                . " FETCH NEXT {$limit[0]} ROWS ONLY";
    }

    /**
     * Processes table unique keys.
     * 
     * @param   array       $column
     * @param   boolean     $alter
     */
    protected function processDBIndexKey($column, $alter)
    {
        foreach ($column as $detail) {
            if (!isset($this->index[strtolower($detail[1])])) {
                throw new NotSupportedException("Index [{$detail[1]}] does not"
                        . " supported by MSSQL or try creating from database"
                        . "console.", null, null, 911045);
            }

            list($columnName, $index) = $detail;

            # Index type.
            $indexType = $this->index[strtolower($index)];

            $on = '(' . $this->quote($columnName) . ')';
            if ($indexType == 'CLUSTERED COLUMNSTORE') {
                $on = $columnName = '';
            }

            # Index Name.
            $index = $this->indexPrefix[strtolower($index)] . '_' .
                    $this->getDBTable() . '_' . $columnName;



            # Adding constraint statement to be executed.
            $this->afterExecution[] = "CREATE {$indexType} INDEX "
                    . trim($index, '_') . " ON " . $this->quote($this->getDBTable()) .
                    $on;
        }
    }

    /**
     * Returns instance of MySQL meta information class.
     * 
     * @return  \Nishchay\Data\Meta\Detail\MssqlMetaDetail
     */
    public function getMetaDetailInstance()
    {
        if ($this->metaInstance === NULL) {
            return ($this->metaInstance = new MssqlMetaDetail($this->connectionName, $this->databaseName));
        }

        return $this->metaInstance;
    }

    /**
     * Prepares update statement by iterating over update related config.
     * 
     * @return string
     */
    protected function prepareUpdate()
    {
        $join = $this->getDetail('join');
        $statement = 'UPDATE ' .
                $this->quote($this->getDetail('table')) .
                $this->prepareUpdateSetPart();

        if (!empty($join)) {
            $statement .= ' FROM ' . $this->prepareTablePart($this->getDetail('table'));
        }

        $statement .= $this->processJoin($join) .
                $this->processConditions($this->getDetail('conditions'));
        return $statement;
    }

    /**
     * Returns statement of start transaction.
     * 
     * @param string $name
     * @return string
     */
    protected function prepareStartTransaction($name = null)
    {
        if ($name !== null) {
            $name = ' ' . $this->quoteColumn($name);
        }
        return 'START TRANSACTION' . $name;
    }

    /**
     * Returns type of int for the DB.
     * 
     * @param int $length
     * @return string
     */
    protected function getTypeOfInt($length)
    {
        $lengths = [
            2 => 'TINYINT',
            4 => 'SMALLINT',
            9 => 'INT',
            20 => 'BIGINT'
        ];

        if (empty($length) || $length === 0) {
            return $lengths[20];
        }
        foreach ($lengths as $max => $type) {
            if ($length <= $max) {
                return $type;
            }
        }
    }

    /**
     * Returns type of string for the DB.
     * 
     * @param int $length
     * @return string
     */
    protected function getTypeOfString($length)
    {
        if ($length === false || $length === 0) {
            $length = 255;
        }
        return ($length === null || $length > 65000) ?
                'TEXT' : ('VARCHAR(' . $length . ')');
    }

    /**
     * Returns type of boolean for the DB.
     * 
     * @return string
     */
    protected function getTypeOfBoolean()
    {
        return $this->getTypeOfInt(1);
    }

    /**
     * Returns type of float for the DB.
     * 
     * @param type $length
     */
    protected function getTypeOfFloat($length)
    {
        if (!empty($length)) {
            $exploaded = explode('.', $length);
            if (count($exploaded) === 2) {
                list($number, $fraction) = explode('.', $length);
                $length = '(' . $number . ',' . $fraction . ')';
            } else {
                $length = '(' . $length . ')';
            }
        } else {
            $length = '';
        }


        return 'NUMERIC' . $length;
    }

    /**
     * Returns type of double for the DB.
     * 
     * @param type $length
     * @return type
     */
    protected function getTypeOfDouble($length)
    {
        return $this->getTypeOfFloat($length);
    }

}
