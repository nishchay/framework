<?php

namespace Nishchay\Data\Builder;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Data\Meta\Detail\MysqlMetaDetail;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class MysqlBuilder extends AbstractBuilder
{

    use MethodInvokerTrait;

    /**
     * Supported Indexes.
     * 
     * @var array 
     */
    protected $index = [
        'unique' => 'UNIQUE',
        'fulltext' => 'FULLTEXT',
        'spatial' => 'SPATIAL'
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
     * Prepares query statement.
     * 
     * @return  string
     */
    protected function prepare()
    {
        $method = 'prepare' . ucfirst($this->getDetail('mode'));
        return $this->invokeMethod([$this, $method]);
    }

    /**
     * Processes table creation columns.
     * 
     * @param   array       $columns
     * @param   boolean     $alter
     */
    protected function processDBCreationColumns($columns, $alter)
    {
        $primaryKey = $alter === false ?
                $this->getDBManagerData('primaryKey') : false;
        foreach ($columns as $config) {

            # We can not find wheather to add or change column but data type of 
            # $name helps us decide what we should do.$name is string means we 
            # need to add column otherwise change column.
            $reflect = 'ADD COLUMN ';
            $name = $config['name'];
            if (is_array($name)) {
                $reflect = 'CHANGE COLUMN ' . $this->quote(key($name)) . ' ';
                $name = current($name);
            }

            $statement = $this->quote($name) . ' ';

            $type = $config['type'];

            if (is_array($type)) {
                $length = current($type);
                if (is_array($length)) {
                    $length = implode(',', $length);
                }

                $statement .= key($type) . '(' . $length . ') ';
            } else {
                $statement .= $type . ' ';
            }

            $default = $config['default'];
            if ($default === false || $default === null) {
                $statement .= ($default === null ?
                        'DEFAULT NULL ' : 'NOT NULL');
            } else {
                $statement .= 'DEFAULT ' .
                        (strpos($default, '^') === 0 ?
                        substr($default, 1) : "'$default'");
            }

            if ($primaryKey === $name) {
                $statement .= ' AUTO_INCREMENT';
            }

            $comment = $config['comment'];
            $statement .= strlen($comment) > 0 ? 'COMMENT "' . $comment . '"' : '';
            $this->dbManagerStatement[] = ($alter ? $reflect : '') . $statement;
        }
    }

    /**
     * Processes table primary key.
     * 
     * @param   string      $column
     * @param   boolean     $alter
     * @return  null
     */
    protected function processDBPrimaryKey($column, $alter)
    {
        if ($column === null) {
            return;
        }

        $this->dbManagerStatement[] = ($alter ? 'ADD ' : '') . 'PRIMARY KEY'
                . ' (' . $this->quote($column) . ')';
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
                        . ' does not supported by MSSQL or try creating from'
                        . ' database console.', null, null, 911046);
            }
            $index = "{$this->index[strtolower($indexType)]} ";
            $index .= ' IDX_' . $this->getDBTable() . '_' . $columnName;
            $this->dbManagerStatement[] = ($alter ? 'ADD ' : '') .
                    $index . '(' . $columnName . ')';
        }
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
            # $config contains
            # $self         = column name of self table on which foreign key
            #                 need to be defined.
            # $parent.      = parent datbase, table and column details. This
            #                 is an array.
            # $on_delete    = What should happens when record get deleted.
            # $on_update    = What should happens when record get updated.

            $statement = ($alter ? 'ADD ' : '');

            # Based on config we are preparing unique key name at database level.
            $parent = $config['parent'];
            $statement .= "CONSTRAINT fk_" . $this->getDBTable() .
                    "_{$parent['table']}_{$parent['column']} ";

            # Self column name.
            $statement .= 'FOREIGN KEY (' . $this->quote($config['self']) . ') ';

            # Now we are preparing reference caluse which point to parent
            # table column. It also possible that self table may need foreign
            # key to another database, in that case database name can come in
            # config.
            $database = isset($parent['database']) ?
                    ($this->quote($parent['database']) . '.') : '';

            # Referencing.
            $statement .= 'REFERENCES ' . $database .
                    $this->quote($parent['table'])
                    . '(' . $this->quote($parent['column']) . ')';

            # Cascading rule.
            $statement .= " ON DELETE {$config['on_delete']} ON UPDATE {$config['on_update']}";
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
            # $config contains
            # $key  = type of key
            # $name = Name of the key.
            $key = $config['key'];
            $name = $this->quoteColumn($config['name']);

            if ($key === 'PRIMARY') {
                $key = 'PRIMARY KEY';
                $name = '';
            }

            $statement = 'DROP ' . $key . ($key === 'FOREIGN' ? ' KEY' : '') .
                    ' ' . $name;
            $this->dbManagerStatement[] = $statement;
        }
    }

    /**
     * Returns instance of MySQL meta information class.
     * 
     * @return  \Nishchay\Data\Meta\Detail\MysqlMetaDetail
     */
    public function getMetaDetailInstance()
    {
        if ($this->metaInstance === NULL) {
            return ($this->metaInstance = new MysqlMetaDetail($this->connectionName, $this->databaseName));
        }

        return $this->metaInstance;
    }

    /**
     * Quotes column name.
     * 
     * @param string $name
     * @return string
     */
    protected function quoteColumn($name)
    {
        return '`' . $name . '`';
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

        # Processing each config detail to prepare statement.
        $this->processDBConfig($mode, $alterMode);

        # This is CREATE OR ALTER TABLE {NAME} statement.
        $statement = strtoupper($mode) . ' TABLE ' .
                $this->quote($this->getDBTable()) . ' ';

        $dbJoinedStatement = $this->getDBJoinedStatement();

        if (empty($dbJoinedStatement)) {
            return '';
        }

        # Create mode requires small brace enclosing to statement.
        if ($alterMode === FALSE) {
            return $statement .= '(' . PHP_EOL .
                    $dbJoinedStatement . ')';
        }

        return $statement . PHP_EOL . $dbJoinedStatement;
    }

    /**
     * 
     * @param   string      $limit
     * @return  string
     */
    protected function processLimit($limit)
    {
        if (empty($limit)) {
            return '';
        }

        list($count, $offset) = explode(',', $limit);
        return PHP_EOL . 'LIMIT ' . $offset . ',' . $count;
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
            3 => 'TINYINT',
            5 => 'SMALLINT',
            7 => 'MEDIUMINT',
            10 => 'INT',
            20 => 'BIGINT'
        ];

        if (empty($length) || $length === 0) {
            return $lengths[20] . '(20)';
        }
        foreach ($lengths as $max => $type) {
            if ($length <= $max) {
                return $type . ' (' . $length . ')';
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
     * Returns type of bool for DB.
     * 
     * @return string
     */
    protected function getTypeOfBool()
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


        return 'DOUBLE' . $length;
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

    /**
     * Returns statement for start transaction.
     * 
     * @param string $name
     * @return string
     */
    protected function prepareStartTransaction($name = null)
    {
        return 'START TRANSACTION';
    }

}
