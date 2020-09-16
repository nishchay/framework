<?php

namespace Nishchay\Data\Builder;

use Exception;
use Nishchay\Data\Meta\Detail\PostgreSQLMetaDetail;
use Nishchay\Data\DatabaseManager;
use Nishchay\Processor\VariableType;

/**
 * PostgreSQL Builder class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class PostgresqlBuilder extends AbstractBuilder
{

    /**
     *
     * @var type 
     */
    protected $comments = [];

    /**
     * Supported Indexes.
     * 
     * @var array 
     */
    protected $index = [
        'unique' => 'UNIQUE',
    ];

    /**
     * Data type mapping to DB.
     * 
     * @var array 
     */
    protected $dataTypes = [
        VariableType::FLOAT => 'NUMERIC', VariableType::DOUBLE => 'NUMERIC',
        VariableType::STRING => ['VARCHAR', 'TEXT'],
        VariableType::DATE => 'DATE', VariableType::DATETIME => 'TIMESTAMP',
        VariableType::DATA_ARRAY => 'TEXT', VariableType::MIXED => 'TEXT'
    ];

    /**
     * 
     * @param type $connectionName
     */
    public function __construct($connectionName)
    {
        parent::__construct($connectionName);
    }

    /**
     * 
     * @return type
     */
    protected function prepare()
    {
        $method = 'prepare' . ucfirst($this->getDetail('mode'));
        return call_user_func([$this, $method]);
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
     * Returns instance of postgreSQL meta information class.
     * 
     * @return  \Nishchay\Data\Meta\Detail\MysqlMetaDetail
     */
    public function getMetaDetailInstance()
    {
        if ($this->metaInstance === NULL) {
            return ($this->metaInstance = new PostgreSQLMetaDetail($this->connectionName, $this->databaseName));
        }

        return $this->metaInstance;
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
            # $name helps us decide what we should do. $name is string means we 
            # need to add column otherwise change column.
            $reflect = 'ALTER COLUMN';
            $name = $config['name'];
            if (is_array($name)) {
                $this->prepareColumnRename(key($name), current($name));
                $name = current($name);
            }
            # Because in alter statement, most of the definition are not allowed
            # in one statement. we will first create column in table then apply
            # other definitions.
            else if ($alter) {
                $this->dbManagerStatement[] = 'ADD COLUMN ' . $this->quoteColumn($name) . ' ' . $columnType;
            }

            $column = $this->quote($name);

            # Column can be set to NOT NULL by specifying $default=FALSE.
            $default = $config['default'];
            if ($default === false || $default === null) {
                $default = ($default === null ? 'DEFAULT NULL' : 'NOT NULL');
            } else {
                # Default value can be used as it is if its being prefixed by
                # ^(caret).
                $default = 'DEFAULT ' . (strpos($default, '^') === 0 ?
                        substr($default, 1) : '\'' . $default . '\'');
            }

            $comment = $config['comment'];
            $this->prepareComment($comment, $column);

            # If table is being created, each definition goes in one line.
            if ($alter === FALSE) {
                $this->dbManagerStatement[] = $column . ' ' . $columnType . ' ' . $default;
                continue;
            }

            if (is_array($config['name'])) {
                $this->dbManagerStatement[] = $reflect . ' ' . $column . ' TYPE ' . $columnType;
            }
            $this->dbManagerStatement[] = $reflect . ' ' . $column . ' SET ' . $default;
        }
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
            $this->dbManagerStatement[] = 'RENAME COLUMN ' .
                    $this->quote($current) . ' TO ' .
                    $this->quote($new);
        }
    }

    /**
     * Prepares comment on column statements.
     * 
     * @param string $comment
     * @param string $column
     */
    private function prepareComment($comment, $column)
    {
        # Comment on column
        if (strlen($comment) > 0) {
            $this->comments[] = 'COMMENT ON COLUMN ' .
                    $this->quote($this->getDBTable())
                    . '.' . $column . ' IS ' . $comment;
        }
    }

    /**
     * Prepare database structure manipulation statement.
     * 
     * @return string
     */
    protected function prepareDBManagerStatement()
    {
        # Before creating statement, let's  find wheather table already
        # exist or not. setting mode to alter or create helps us making
        # 'create' or 'alter' statement.
        $mode = $this->getDBMode();
        $alterMode = $this->isDBAlterMode();
        $this->processDBConfig($mode, $alterMode);
        $statement = strtoupper($mode) . ' TABLE ' .
                $this->quote($this->getDBTable()) . ' ';

        # Create mode requires small brace enclosing to statement.
        if ($alterMode === false) {
            $dbJoinedStatement = $this->getDBJoinedStatement();
            if (empty($dbJoinedStatement)) {
                return '';
            }
            return $statement .= '(' . PHP_EOL .
                    $dbJoinedStatement . ')';
        }

        return $this->prefixDBStatement($statement);
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
        return false;
    }

    /**
     * Executes query generated from Database Manager.
     * 
     * @param   \Nishchay\Data\DatabaseManager      $databaseManager
     * @return  mixed
     */
    public function executeDBManager(DatabaseManager $databaseManager)
    {
        $this->setDBManagerData($databaseManager);
        # If it returns string, it means there is only one statement
        # to be executed. If the table is being created there will be only
        # one statment.
        if (($statement = $this->prepareDBManagerStatement()) !== false && !empty($statement)) {
            $this->execute($statement);
            goto COMMENT;
        }
        # For altering table, there will be more than one statement. So we
        # here executing each statement one by one.
        foreach ($this->dbManagerStatement as $statement) {
            $this->execute($statement);
        }

        COMMENT:
        foreach ($this->comments as $statement) {
            $this->execute($statement);
        }
        return !empty($this->dbManagerStatement) || !empty($this->comments);
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
        if ($column === null) {
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
            $constraintName = 'fk_' . $this->getDBTable() . '_' . $parent['table'] . '_' . $parent['column'];
            $statement .= 'CONSTRAINT ' . $this->quote($constraintName);

            # Self column name.
            $self = $config['self'];
            $statement .= 'FOREIGN KEY (' . $this->quote($self) . ') ';

            # Now we are preparing reference caluse which point to parent
            # table column. It also possible that self table may need
            # foreign key to another schema, in that case schema name
            # can come in config.
            $schema = isset($parent['schema']) ?
                    ($this->quote($parent['schema']) . '.') : '';
            $statement .= 'REFERENCES ' . $schema .
                    $this->quote($parent['table'])
                    . '(' . $this->quote($parent['column']) . ') MATCH SIMPLE ';

            # Cascading rule.
            $statement .= "ON DELETE {$config['on_delete']} ON UPDATE {$config['on_update']}";
            $this->dbManagerStatement[] = $statement;
        }
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

            list($columnName, $indexType) = $detail;
            if (!isset($this->index[strtolower($indexType)])) {
                throw new NotSupportedException('Index [' . $indexType . ']'
                        . ' does not supported by PostgreSQL or try creating from'
                        . ' database console.', null, null, 911047);
            }

            # Index Name.
            $index = $this->quote('IDX_' . $this->getDBTable() . '_' . $columnName);

            # Index type.
            $index .= " {$this->index[strtolower($indexType)]} ";

            # Adding constraint statement to be executed.
            $this->dbManagerStatement[] = ($alter ? 'ADD ' : '') . 'CONSTRAINT '
                    . $index . '(' . $this->quote($columnName) . ')';
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
            $this->dbManagerStatement[] = 'DROP CONSTRAINT IF EXISTS ' . $this->quote($config['name']);
        }
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
        return PHP_EOL . 'LIMIT ' . $limit[0] .
                (isset($limit[1]) ? (' OFFSET ' . $limit[1]) : '');
    }

    /**
     * Returns statement for start transaction.
     * 
     * @param string $name
     * @return string
     */
    protected function prepareStartTransaction($name = null)
    {
        return 'BEGIN';
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
            4 => 'SMALLINT',
            9 => 'INTEGER',
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
        return 'BOOLEAN';
    }
    
    /**
     * Returns type of bool for DB.
     * 
     * @return string
     */
    protected function getTypeOfBool()
    {
        return $this->getTypeOfBoolean();
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
