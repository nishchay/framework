<?php

namespace Nishchay\Data\Builder;

use Nishchay;
use ReflectionProperty;
use Nishchay\Data\Connection\AbstractConnection;
use Nishchay\Utility\Coding;
use Nishchay\Data\Query;
use Nishchay\Data\DatabaseManager;
use Nishchay\Utility\StringUtility;
use Nishchay\Processor\VariableType;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * Base Builder class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractBuilder extends AbstractConnection
{

    use MethodInvokerTrait;

    /**
     * Insert statement name.
     * 
     */
    const INSERT = 'insert';

    /**
     * Insert many statement name.
     */
    const INSERT_MULTIPLE = 'insertMultiple';

    /**
     * Update statement name.
     * 
     */
    const UPDATE = 'update';

    /**
     * Select statement name.
     * 
     */
    const SELECT = 'select';

    /**
     * Delete statement name.
     */
    const DELETE = 'delete';

    /**
     * Query Data as set Query class.
     * 
     * @var array
     */
    protected $queryDetail;

    /**
     * Connection management data as set in ConnectionManager class.
     * 
     * @var array 
     */
    protected $dbManagerData;

    /**
     * Select statement part orders.
     * 
     * @var array 
     */
    protected $select = ['columns', 'table', 'join', 'conditions',
        'groupBy', 'having', 'orderBy', 'limit'];

    /**
     * Update config.
     * 
     * @var array 
     */
    protected $updateConfig = ['conditions', 'valueColumns', 'table'];

    /**
     *
     * @var array 
     */
    protected $dbManagerConfig = ['creationColumns', 'removalColumns',
        'primaryKey', 'indexKey', 'foreignKey', 'removalKey'];

    /**
     * Data type mapping to DB.
     * 
     * @var array 
     */
    protected $dataTypes = [
        VariableType::FLOAT => 'DOUBLE', VariableType::DOUBLE => 'DOUBLE',
        VariableType::STRING => ['VARCHAR', 'TEXT'],
        VariableType::DATE => 'DATE', VariableType::DATETIME => 'DATETIME',
        VariableType::DATA_ARRAY => 'TEXT', VariableType::MIXED => 'TEXT'
    ];

    /**
     *
     * @var array 
     */
    protected $dbManagerStatement = [];

    /**
     * 
     * @param type $connectionName
     */
    public function __construct($connectionName)
    {
        parent::__construct($connectionName);
    }

    /**
     * Set data value from Query class.
     * 
     * @param \Nishchay\Data\Query $query
     */
    protected function setDataValue(Query $query)
    {
        $this->queryDetail = Coding::getAsArray($query);
    }

    /**
     * Set Connection management data.
     * 
     * @param \Nishchay\Data\DatabaseManager $databaseManager
     */
    protected function setDBManagerData(DatabaseManager $databaseManager)
    {
        $this->dbManagerData = Coding::getAsArray($databaseManager);
    }

    /**
     * Returns Query configuration detail.
     * 
     * @param   string      $name
     * @return  mixed
     */
    protected function getDetail($name)
    {
        return array_key_exists($name, $this->queryDetail) ?
                $this->queryDetail[$name] : false;
    }

    /**
     * Returns Connection data information of given property.
     * 
     * @param   string      $name
     * @return  string
     */
    protected function getDBManagerData($name)
    {
        return array_key_exists($name, $this->dbManagerData) ?
                $this->dbManagerData[$name] : FALSE;
    }

    /**
     * Returns table form Connection management class.
     * 
     * @return string
     */
    protected function getDBTable()
    {
        return $this->getDBManagerData('table');
    }

    /**
     * Returns Connection joined statement.
     * 
     * @return string
     */
    protected function getDBJoinedStatement()
    {
        return implode(',' . PHP_EOL, $this->dbManagerStatement);
    }

    /**
     * Prepares select statement by iterating over select applicable config.
     * 
     * @return string
     */
    protected function prepareSelect()
    {
        $parts = [];
        foreach ($this->select as $config_name) {
            $parts[] = call_user_func([$this,
                'process' . ucfirst($config_name)
                    ], $this->getDetail($config_name));
        }
        foreach ($this->getDetail('union') as $statement) {
            $parts[] = PHP_EOL . $statement;
        }
        return trim(implode(' ', $parts));
    }

    /**
     * Processes columns to set select columns only.
     * 
     * @param   string      $column
     * @return  string
     */
    protected function processColumns($column)
    {
        return 'SELECT ' . (empty($column) ? '*' : $column);
    }

    /**
     * Return statement for from table part.
     * 
     * @param   string      $table
     * @return  string
     */
    protected function processTable($table)
    {
        return PHP_EOL . 'FROM ' . $this->prepareTablePart($table);
    }

    /**
     * Prepare table part of SELECT, UPDATE & DELETE query.
     * 
     * @param string $table
     * @return string
     */
    protected function prepareTablePart($table)
    {
        $table = $this->quote($table);
        return $this->getDetail('mainTable') .
                ($table === $this->getDetail('mainTable') ?
                '' : (' AS ' . $table));
    }

    /**
     * Processes conditions array to generate WHERE clause.
     * 
     * @param   array       $conditions
     * @return  string
     */
    protected function processConditions($conditions)
    {
        $conditions = $this->factorizeWhere($conditions);
        return count($conditions) > 0 ? (PHP_EOL . 'WHERE ' .
                implode(' AND ', $conditions)) : '';
    }

    /**
     * Processes having clause.
     * 
     * @param   array       $clause
     * @return  string
     */
    protected function processHaving($clause)
    {
        return count($clause) > 0 ?
                (PHP_EOL . 'HAVING ' . implode(' AND ', $clause)) : '';
    }

    /**
     * Processes group by clause.
     * 
     * @param   string      $groupBy
     * @return  string
     */
    protected function processGroupBy($groupBy)
    {
        return empty($groupBy) ? '' : (PHP_EOL . 'GROUP BY ' . $groupBy);
    }

    /**
     * Processes order by clause.
     * 
     * @param   string      $orderBy
     * @return  string
     */
    protected function processOrderBy($orderBy)
    {
        return empty($orderBy) ? '' : (PHP_EOL . 'ORDER BY ' . $orderBy);
    }

    /**
     * Processes join config to prepare join statement.
     * 
     * @param       array       $join
     * @return      string
     */
    protected function processJoin($join)
    {
        $statement = '';
        foreach ($join as $config) {
            $statement .= PHP_EOL . $config['type'] . ' JOIN ' . $config['table'];
            $statement .= empty($config['on']) ? '' : ' ON ' . $config['on'];
        }
        return $statement;
    }

    /**
     * Calls Connection process of given config name.
     * 
     * @param type $confgiName
     * @param type $alterMode
     */
    protected function callDBProcess($confgiName, $alterMode)
    {
        $method = 'processDB' . StringUtility::toCamelCase($confgiName);
        if ($this->isCallbackExist([$this, $method])) {
            $this->invokeMethod([$this, $method], [$this->getDBManagerData($confgiName), $alterMode]);
        }
    }

    /**
     * Process table removal columns.
     * 
     * @param array $columns
     */
    protected function processDBRemovalColumns($columns)
    {
        foreach ($columns as $name) {
            $this->dbManagerStatement[] = 'DROP COLUMN ' . $this->quote($name);
        }
    }

    /**
     * Prepares insert statement.
     * 
     * @return string
     */
    protected function prepareInsert()
    {
        $values = $this->getDetail('valueColumns');

        # Iterating over each value columns to quote column name.
        foreach ($values as $key => $value) {
            unset($values[$key]);
            $values[$this->quote($key)] = $value;
        }

        $statement = 'INSERT INTO ' . $this->quote($this->getDetail('table'));

        $statement .= '(' . implode(',', array_keys($values)) . ') VALUES(' .
                implode(',', $values) . ')';
        return $statement;
    }

    /**
     * Returns insert many statement.
     * 
     * @return string
     */
    protected function prepareInsertMultiple()
    {
        $detail = $this->getDetail('multipleValueColumns');

        $columns = [];

        # Iterating over each columns to quote column name
        foreach ($detail['columns'] as $name) {
            $columns[] = $this->quote($name);
        }

        # Preparing statement
        $statement = 'INSERT INTO ' . $this->quote($this->getDetail('table')) . PHP_EOL .
                '(' . implode(',', $columns) . ')' .
                ' VALUES ' . PHP_EOL;

        # Now iterating over each values to prepare value part of insert
        foreach ($detail['values'] as $value) {
            $statement .= '(' . implode(',', $value) . '),';
        }

        # Because we adding comma after value part, trailing comma should be removed.
        return trim($statement, ',');
    }

    /**
     * Prepares update statement by iterating over update related config.
     * 
     * @return string
     */
    protected function prepareUpdate()
    {
        $statement = 'UPDATE ' .
                $this->prepareTablePart($this->getDetail('table')) .
                $this->processJoin($this->getDetail('join')) .
                $this->prepareUpdateSetPart() .
                $this->processConditions($this->getDetail('conditions'));
        return $statement;
    }

    /**
     * Returns statement for rollback.
     * 
     * @return string
     */
    protected function prepareRollback()
    {
        $toSavepoint = '';
        if (($name = $this->getDetail('name')) !== null) {
            $toSavepoint = ' TO ' . $this->quote($name);
        }
        return 'ROLLBACK' . $toSavepoint;
    }

    /**
     * Returns statement for commit.
     * 
     * @return string
     */
    protected function prepareCommit()
    {
        return 'COMMIT';
    }

    /**
     * Returns value column SET statement for update query.
     * 
     * @return string
     */
    protected function prepareUpdateSetPart()
    {
        $columns = [];
        foreach ($this->getDetail('valueColumns') as $key => $value) {
            $columns[] = $this->quote($key) . ' = ' . $value;
        }
        return PHP_EOL . 'SET ' . implode(',', $columns);
    }

    /**
     * Prepares DELETE statement.
     * 
     * @return string
     */
    protected function prepareDelete()
    {
        $statement = 'DELETE ';
        $join = $this->getDetail('join');
        if (count($join) > 0) {
            $statement .= $this->quoteColumn($this->getDetail('table'));
        }
        $statement .= $this->processTable($this->getDetail('table')) .
                $this->processJoin($join) .
                $this->processConditions($this->getDetail('conditions'));
        return $statement;
    }

    /**
     * Returns TRUE if table already exists.
     * This is to find if we have to create or alter table.
     * 
     * @return boolean
     */
    protected function isDBAlterMode()
    {
        return $this->getDBMode() === 'alter';
    }

    /**
     * Returns 'create' if table is to create otherwise 'alter'.
     * 
     * @return type
     */
    protected function getDBMode()
    {
        return $this->getDBManagerData('tableExist') ?
                'alter' : 'create';
    }

    /**
     * Processes each Connection config from Connection Management class.
     * 
     * @param string $mode
     * @param boolean $alterMode
     */
    protected function processDBConfig($mode, $alterMode)
    {
        $this->dbManagerStatement = [];
        foreach ($this->dbManagerConfig as $confgiName) {
            # No need of removal config while mode is create.
            if ($mode === 'create' && strpos($confgiName, 'removal') === 0) {
                continue;
            }
            $this->callDBProcess($confgiName, $alterMode);
        }
    }

    /**
     * Factorize where conditions. 469919
     * 
     * @param   array      $condition
     * @return  array
     */
    protected function factorizeWhere($condition)
    {
        if (count($condition) > 1) {
            foreach ($condition as $key => $value) {
                $condition[$key] = '(' . $value . ')';
            }
        }
        return $condition;
    }

    /**
     * Quotes expression.
     * 
     * @param       string          $name
     * @return      string
     */
    public function quote($name)
    {
        if (strpos($name, '.') !== false) {
            $column = array_map('trim', explode('.', $name));

            foreach ($column as $key => $name) {
                $column[$key] = $name === '*' ?
                        '*' : $this->quoteColumn($name);
            }

            return implode('.', $column);
        } else if (trim($name) === '*') {
            return $name;
        }
        return $this->quoteColumn($name);
    }

    /**
     * Executes query generated from Query Builder.
     *  
     * @param   Nishchay\Data\Query  $query
     * @return  mixed
     */
    public function executeFromBuilder(Query $query)
    {
        $rawSql = $this->getSQL($query);

        if ($this->getDetail('mode') === self::SELECT && $this->getDetail('cacheKey') !== false) {
            $fromCache = $this->getFromCache($query, $rawSql);
            if (!empty($fromCache)) {
                return $fromCache;
            }
        }

        return $this->execute($rawSql, $this->getDetail('binds'));
    }

    /**
     * If query builder has set cacheKey for query being executed and 
     * cacheConfig exists for database then item is fetched from cache.
     * 
     * @param string $rawSql
     * @return mixed
     */
    private function getFromCache($query, $rawSql)
    {
        # Fetching cache name of cache config which should be used for fetching
        # result from cache.
        $cacheName = $this->getCacheName();

        # If cache name is null it means cache not to be used for query being
        # executed for connection.
        if ($cacheName === null) {
            return null;
        }
        $cacheKey = $this->getCacheKey($rawSql);
        $this->updateCacheKeyInQuery($query, $cacheKey);

        # Fetching from cache.
        return Nishchay::getCache($cacheName)->get($cacheKey);
    }

    /**
     * Updates generated cacheKey property of Query class.
     * 
     * @param Query $query
     * @param string $cacheKey
     */
    private function updateCacheKeyInQuery($query, $cacheKey)
    {
        $reflection = new ReflectionProperty(Query::class, 'cacheKey');
        $reflection->setAccessible(true);
        $reflection->setValue($query, $cacheKey);
    }

    /**
     * Generates cacheKey if config of query builder is set to configured based on query.
     * 
     * @param string $rawSql
     * @return string
     * @throws ApplicationException
     */
    private function getCacheKey($rawSql)
    {
        $cacheKey = $this->getDetail('cacheKey');

        # If it is true it means cacheKey need to generated from query statement
        if ($cacheKey === true) {
            return md5($this->replaceBinds($rawSql));
        }

        # If cacheKey is empty or is not string we will produce an error.
        if (empty($cacheKey) || !is_string($cacheKey)) {
            throw new ApplicationException('Invalid cache key');
        }

        return $cacheKey;
    }

    /**
     * Returns SQL Statement.
     * 
     * @param Query $query
     * @return string
     */
    public function getSQL(Query $query, $raw = false)
    {
        $this->setDataValue($query);
        $this->setMode();
        $queryStatement = $this->prepare();
        return $raw ?
                $this->replaceBinds($queryStatement) : $queryStatement;
    }

    /**
     * Replaces bind placeholder with its value in query statement and returns it.
     * 
     * @param string $queryStatement
     * @return string
     */
    private function replaceBinds($queryStatement)
    {
        $binds = array_reverse($this->getDetail('binds'));
        foreach ($binds as $name => $value) {
            $value = is_string($value) ? '\'' . $value . '\'' : $value;
            $queryStatement = str_replace(':' . $name, $value, $queryStatement);
        }
        return $queryStatement;
    }

    /**
     * Sets mode of SQL statement.
     * 
     * @return string
     */
    private function setMode()
    {
        if (!empty($this->getDetail('mode'))) {
            return;
        }
        $mode = self::SELECT;
        switch (true) {
            case $this->isUpdateMode():
                $mode = self::UPDATE;
                break;
            case $this->isInsertManyMode():
                $mode = self::INSERT_MULTIPLE;
                break;
            case $this->isInsertMode():
                $mode = self::INSERT;
                break;
            default :
                break;
        }

        $this->queryDetail['mode'] = $mode;
    }

    /**
     * Returns TRUE if mode is update.
     * 
     * @return boolean
     */
    private function isUpdateMode()
    {
        $valueColumns = $this->getDetail('valueColumns');
        $conditions = $this->getDetail('conditions');

        # We consider statement is UPDATE if there's both value to be set and
        # conditions are set.
        return !empty($valueColumns) && !empty($conditions);
    }

    /**
     * Returns TRUE if mode is insert.
     * 
     * @return boolean
     */
    private function isInsertMode()
    {
        $valueColumns = $this->getDetail('valueColumns');
        $conditions = $this->getDetail('conditions');

        # We consider statement is INSERT if there's no condition and value
        # to be inserted is set.
        return !empty($valueColumns) && empty($conditions);
    }

    /**
     * Returns TRUE if mode is multiple insert.
     * 
     * @return boolean
     */
    private function isInsertManyMode()
    {
        $valueColumns = $this->getDetail('multipleValueColumns');
        return !empty($valueColumns);
    }

    /**
     * Returns TRUE if mode is remove.
     * 
     * @return boolean
     */
    private function isDeleteMode()
    {
        $columns = $this->getDetail('columns');
        $valueColumns = $this->getDetail('valueColumns');
        $conditions = $this->getDetail('conditions');

        # We consider statment is DELETE if there's nothing to update and
        # conditions has been set
        return empty($columns) && empty($valueColumns) && !empty($conditions);
    }

    /**
     * Executes query generated from Database Manager.
     * 
     * @param   \Nishchay\Data\DatabaseManager      $databaseManager
     * @return  mixed
     */
    public function executeDBManager(DatabaseManager $databaseManager)
    {
        $sql = $this->getDBSQL($databaseManager);

        # For database other than MySQL, database management statements are
        # generated more than one and are executed once all statements are
        # created. For MySQL only statement is generated and is executed here
        # only.
        if (is_string($sql) && !empty($sql)) {
            return $this->execute($sql);
        }

        return true;
    }

    /**
     * Returns SQL Statement for database manager.
     * 
     * @param DatabaseManager $databaseManager
     * @return type
     */
    public function getDBSQL(DatabaseManager $databaseManager)
    {
        $this->setDBManagerData($databaseManager);
        $statement = $this->prepareDBManagerStatement();
        if ($statement !== false) {
            return $statement;
        }

        return $this->dbManagerStatement;
    }

    /**
     * Returns type for the DB.
     * 
     * @param string $type
     * @param int $length
     * @return string
     */
    public function getType($type, $length)
    {
        $type = strtolower($type);
        if (in_array($type, [VariableType::DATA_ARRAY, VariableType::DATE, VariableType::DATETIME,
                    VariableType::MIXED])) {
            return $this->dataTypes[$type];
        }
        $method = 'getTypeOf' . (ucfirst($type));

        return $this->invokeMethod([$this, $method], [$length]);
    }

    /**
     * Prepares query.
     * 
     * @return  string
     */
    protected abstract function prepare();

    /**
     * Prepare database structure manipulation statement.
     * 
     * @return string
     */
    protected abstract function prepareDBManagerStatement();

    /**
     * Quotes column name.
     * 
     * @param string $name
     * @return string
     */
    protected abstract function quoteColumn($name);

    /**
     * Prepares statement for starting transaction.
     * 
     * @param string $name
     */
    protected abstract function prepareStartTransaction($name = null);
}
