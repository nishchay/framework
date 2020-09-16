<?php

namespace Nishchay\Data;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use PDO;
use PDOStatement;
use DateTime;
use Nishchay\Data\Connection\Connection;
use Nishchay\Utility\StringUtility;
use Nishchay\Utility\DateUtility;
use Nishchay\Data\Builder\AbstractBuilder;

/**
 * Query class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Query
{

    /**
     * Operator for inner join.
     * 
     */
    const INNER_JOIN_OPERATOR = '><';

    /**
     * Inner join literal
     */
    const INNER_JOIN = '[><]';

    /**
     * Operator for left join.
     */
    const LEFT_JOIN_OPERATOR = '<';

    /**
     * Left join literal.
     */
    const LEFT_JOIN = '[<]';

    /**
     * Operator for right join.
     */
    const RIGHT_JOIN_OPERATOR = '>';

    /**
     * Right join literal.
     */
    const RIGHT_JOIN = '[>]';

    /**
     * Operator for cross join.
     */
    const CROSS_JOIN_OPERATOR = '<>';

    /**
     * Cross join literal.
     */
    const CROSS_JOIN = '[<>]';

    /**
     * Operator for full join.
     */
    const FULL_JOIN_OPERATOR = '=';

    /**
     * Full join literal.
     */
    const FULL_JOIN = '[=]';

    /**
     * To use right operand as value.
     */
    const AS_VALUE = '$';

    /**
     * To use right operand as column.
     */
    const AS_COLUMN = '#';

    /**
     * To use right operand as it is.
     */
    const AS_IT_IS = '~';

    /**
     * To use left operand as it is.
     */
    const LEFT_AS_IT_IS = '^';

    /**
     * To produce IN clause
     */
    const IN = '[+]';

    /**
     * IN clause operator.
     */
    const IN_OPERATOR = '+';

    /**
     * To produce NOT IN clause
     */
    const NOT_IN = '[!]';

    /**
     * NOT IN clause operator.
     */
    const NOT_IN_OPERATOR = '!';

    /**
     * To produce between clause.
     */
    const BETWEEN = '[><]';

    /**
     * Between clause operator
     */
    const BETWEEN_OPERATOR = '><';

    /**
     * Join on clause joiner
     */
    const ON_JOINER = '!';

    /**
     * Connection name.
     * 
     * @var string 
     */
    private $connectionName;

    /**
     * Selection columns.
     * 
     * @var string 
     */
    private $columns = '';

    /**
     * Columns to be updated or insert.
     * 
     * @var array 
     */
    private $valueColumns = [];

    /**
     * Columns with multiple columns to be updated.
     * 
     * @var array
     */
    private $multipleValueColumns = [];

    /**
     * Main table name.
     * 
     * @var type 
     */
    private $mainTable;

    /**
     * Table name.
     * This is alias of table.
     * 
     * @var string 
     */
    private $table = '';

    /**
     * Current mode of query(select,update,insert or remove).
     * 
     * @var string 
     */
    private $mode;

    /**
     * Conditional clause.
     * 
     * @var array 
     */
    private $conditions = [];

    /**
     * Conditional operator.
     * 
     * @var array 
     */
    private static $conditionalOperator = ['>', '<', '=', '!', 'like'];

    /**
     * Regex for conditional expression.
     * 
     * @var string 
     */
    private static $conditionalRegex = '#(([\>|\<|\=|\!]+)|like)$#i';

    /**
     * Regex for array based clause.
     * 
     * @var string 
     */
    private static $arrayBasedRegex = '#^(.*)\[(\!|\+|\>\<)\][\^]?$#';

    /**
     * Binding values.
     * 
     * @var array 
     */
    private $binds = [];

    /**
     * Binding parameter count value.
     * 
     * @var int 
     */
    private $bindCount = 1;

    /**
     * Total instance count of this class.
     * 
     * @var int 
     */
    private static $instanceCount = 0;

    /**
     * Instance number of this class.
     * 
     * @var int 
     */
    private $instanceNumber;

    /**
     * Record limitation.
     * 
     * @var string 
     */
    private $limit = '';

    /**
     * Join tables.
     * 
     * @var array 
     */
    private $join = [];

    /**
     * Having clause.
     * 
     * @var array 
     */
    private $having = [];

    /**
     * Group by.
     * 
     * @var string 
     */
    private $groupBy = '';

    /**
     * Order by.
     * 
     * @var string 
     */
    private $orderBy = '';

    /**
     * Union query.
     * 
     * @var array 
     */
    private $union = [];

    /**
     * Cache key.
     * 
     * @var boolean|string
     */
    private $cacheKey = false;

    /**
     * Last cache key.
     * 
     * @var string|boolean 
     */
    private $lastCacheKey = false;

    /**
     * Cache expiration time.
     * 
     * @var int
     */
    private $cacheExpiration = 0;

    /**
     * Flag to indicate if last record retrieved from cache or DB.
     * 
     * @var boolean
     */
    private $isLastResultFromCache;

    /**
     * Name used for save point name and rollback to save point name.
     * 
     * @var string
     */
    private $name;

    /**
     * 
     * @param string $connection
     */
    public function __construct($connection = null)
    {
        $this->connectionName = $connection === null ? Connection::getDefaultConnectionName() : $connection;
        $this->instanceNumber = ++self::$instanceCount;
    }

    /**
     * Returns connection name which is used for this builder.
     * 
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Sets table name.
     * 
     * @param   string $table
     * @return  Nishchay\Data\Query
     */
    public function setTable($table, $alias = null)
    {

        if ($table instanceof Query) {
            if ($alias === null) {
                throw new ApplicationException('Alias required when subquery'
                        . ' is used as from table.', 1, null, 911084);
            }
            $mainTable = $this->addQueryToThis($table);
        } else {
            $mainTable = $this->quote($table);
        }

        if ($alias === null) {
            $alias = StringUtility::getExplodeLast('.', $table);
        }
        $this->mainTable = $mainTable;
        $this->table = $alias;
        return $this;
    }

    /**
     * Returns table name.
     * 
     * @return string
     */
    private function getTable()
    {
        return $this->table;
    }

    /**
     * Set column for selection.
     * 
     * @param   string|array $column
     * @return  Nishchay\Data\Query
     */
    public function setColumn($column)
    {
        $column = !is_array($column) ? [$column] : $column;
        $preparedColumn = [];
        foreach ($column as $alias => $field) {
            if ($field instanceof Query) {
                $field = $this->addQueryToThis($field);
            } else {
                # Ignoring whatever in $field if $alias is prefixed by ^(caret)
                if (substr($alias, -1, 1) === self::AS_IT_IS) {
                    $alias = substr($alias, 0, strlen($alias) - 1);
                } else {
                    $field = $this->quote($field);
                }
            }
            $preparedColumn[$alias] = $field . (!is_numeric($alias) ? ' AS ' . $this->quote($alias) : '');
        }

        $this->columns .= (empty($this->columns) ? '' : ',') . implode(',', $preparedColumn);
        return $this;
    }

    /**
     * Set column with value for update or insert.
     * 
     * @param   string|array $columns
     * @param   string $value
     * @return  \Nishchay\Data\Query
     */
    public function setColumnWithValue($columns, $value = null)
    {
        $columns = is_array($columns) ? $columns : [$columns => $value];
        foreach ($columns as $colunnName => $value) {
            $last = substr($colunnName, -1);
            if (in_array($last, [self::AS_IT_IS, self::AS_COLUMN])) {
                if ($last === self::AS_COLUMN) {
                    $value = $this->quote($value);
                }
                $colunnName = substr($colunnName, 0, -1);
            } else {
                $value = $this->bindValue($value);
            }
            $this->valueColumns[$colunnName] = $value;
        }
        return $this;
    }

    public function setColumnWithMultipleValue($columns, $values)
    {
        $count = count($columns);
        $this->multipleValueColumns['columns'] = $columns;
        $multipleValues = [];
        foreach ($values as $index => $value) {
            if (count($value) !== $count) {
                throw new ApplicationException('Column and value count mismatched for multiple insert.', 1, null, 911085);
            }

            # Iterating over each to bind value
            foreach ($value as $val) {
                $multipleValues[$index][] = $this->bindValue($val);
            }
        }
        $this->multipleValueColumns['values'] = $multipleValues;
        return $this;
    }

    /**
     * Sets where clause.
     * 
     * @param string|array $column
     * @param string|array $value
     * @return \Nishchay\Data\Query
     */
    public function setCondition($column, $value = '')
    {
        $column = is_string($column) ? [$column => $value] : $column;
        $this->conditions[] = $this->prepareCondition($column);
        return $this;
    }

    /**
     * Sets limit.
     * 
     * @param   int $limit
     * @param   int $offset
     * @return  Nishchay\Data\Query
     */
    public function setLimit($limit, $offset = 0)
    {
        $this->limit = $limit . ',' . $offset;
        return $this;
    }

    /**
     * Add JOIN.
     * 
     * @param   string $table
     * @param   string $on
     * @param   string $type
     * @return  Nishchay\Data\Query
     */
    public function addJoin($join)
    {
        $types = [
            self::INNER_JOIN_OPERATOR => 'INNER',
            self::LEFT_JOIN_OPERATOR => 'LEFT',
            self::RIGHT_JOIN_OPERATOR => 'RIGHT',
            self::CROSS_JOIN_OPERATOR => 'CROSS',
            self::FULL_JOIN_OPERATOR => 'FULL'
        ];
        foreach ($join as $tableName => $joinCondition) {
            # Finding join type from chracter at start of key.
            # Join type can be any from $types array.
            $joinType = self::LEFT_JOIN_OPERATOR;
            if (preg_match('#^\[(\>\<|\>|\<|\<\>|\=)\](.*)#', $tableName, $match)) {
                $tableName = $match[2];
                $joinType = $match[1];
            }

            # We need main table name and table name too. Here table name 
            # is alias of main table. Let's say below pattern does not match,
            # then both main table and table name will be the same.
            $mainTable = StringUtility::getExplodeLast('.', $tableName);
            if (preg_match('#^(.*)\((.*)\)$#', $tableName, $match)) {
                $tableName = $match[1];
                $mainTable = $match[2];
            }

            # Constructing join ON clause.
            $on = [];

            # If the join condition is string we will take join_condition as
            # column name for both main table and joining table.
            if (is_string($joinCondition)) {
                $on = $this->getAssigneeValue($mainTable . '.' . $joinCondition . '#', $this->getTable() . '.' . $joinCondition);
            }
            # Formating join condition.
            else if (is_array($joinCondition)) {
                $on = $this->getJoinCondition($joinCondition, $mainTable);
            }

            $table = $this->quote($tableName);

            if ($tableName !== $mainTable) {
                $table .= ' AS ' . $this->quote($mainTable);
            }

            $this->join[] = [
                'table' => $table,
                'on' => $on,
                'type' => $types[$joinType]
            ];
        }
        return $this;
    }

    /**
     * Returns join condition after preparing it properly.
     * 
     * @param array $joinCondition
     * @param string $tableName
     * @return array
     */
    private function getJoinCondition($joinCondition, $tableName)
    {
        $prepared = [];
        foreach ($joinCondition as $left => $right) {
            if (is_numeric($left)) {
                if (is_array($right)) {
                    $prepared[] = '(' . $this->getJoinCondition($right, $tableName) . ')';
                    continue;
                }
                $left = $right = $right;
            }

            if ($right instanceof Query || is_array($right)) {
                preg_match(self::$arrayBasedRegex, $left, $match);

                if (isset($match[2])) {
                    if (substr($match[0], -1, 1) !== self::LEFT_AS_IT_IS) {
                        $match[1] = strpos($match[1], '.') === FALSE ?
                                $tableName . '.' . $match[1] : $match[1];
                    }
                    $prepared[] = $this->getArrayBasedClause($match, $right);
                    continue;
                }
            }
            $last = substr($left, -1);

            # $ to put at last to make right operand take as value.
            # Removing $ now as we will pass these two to getAssigneeValue and
            # this method by default takes right operand as value so no need to 
            # tell explicitly.
            if ($last === self::AS_VALUE) {
                $left = substr($left, 0, -1);
            }

            # If none of this found, we take that both side is column.
            # In join conditional expression, we don't need to tell right 
            # operand is column as in join right operand is taken as column
            # by default.
            if (!in_array($last, [self::AS_IT_IS, self::AS_VALUE])) {
                $left .= self::AS_COLUMN;
            }

            # ~ To make right operand to be use as it as and $ to make right
            # oeprand take as value.
            if ($last !== self::AS_IT_IS && $last !== self::AS_VALUE) {
                $right = strpos($right, '.') === FALSE ? $this->getTable() .
                        '.' . $right : $right;
            }

            # Now finally formatting left operand, if ^ is at second last
            # position we will not do any thing with left operand.
            if (substr($left, -2, 1) !== self::LEFT_AS_IT_IS) {
                $left = strpos($left, '.') === FALSE ?
                        $tableName . '.' . $left : $left;
            }

            JOIN_ON:
            $prepared[] = $this->getAssigneeValue($left, $right);
        }

        end($joinCondition);
        $key = key($joinCondition);
        $value = current($joinCondition);

        if (is_int($key) && is_string($value) && strpos($value, self::ON_JOINER) === 0) {
            array_pop($prepared);
            $joinCondition[$key] = substr($value, 1);
        } else {
            $joinCondition[] = 'AND';
        }

        return $this->combineCondition($joinCondition, $prepared);
    }

    /**
     * Checks if value columns or multiple value column has been. If it not
     * set then exception is thrown.
     * 
     * @return $this
     * @throws ApplicationException
     */
    private function checkValueColumns()
    {
        if (empty($this->valueColumns)) {
            if ($this->mode !== AbstractBuilder::UPDATE && !empty($this->multipleValueColumns)) {
                return $this;
            }
            throw new ApplicationException('Column with its value are not set.', 2, null, 911086);
        }
        return $this;
    }

    /**
     * Throws exception if condition is not set and update or remove without condition is not enabled.
     * 
     * @param string $name
     * @return $this
     * @throws ApplicationException
     */
    protected function checkNoCondition($name)
    {
        if (Nishchay::getConfig('database.' . $name) !== true && empty($this->conditions)) {
            throw new ApplicationException('Condition is required to' .
                    ($this->mode === AbstractBuilder::DELETE ? 'delete' : 'update')
                    . ' record.', 2, null, 911087);
        }

        return $this;
    }

    /**
     * Executes insert query.
     * 
     * @return  int|boolean
     */
    public function insert()
    {
        $this->checkValueColumns();
        $this->mode = !empty($this->multipleValueColumns) ?
                AbstractBuilder::INSERT_MULTIPLE : AbstractBuilder::INSERT;
        return $this->run();
    }

    /**
     * Executes DELETE query.
     * 
     * @return  int|boolean
     */
    public function remove()
    {
        $this->mode = AbstractBuilder::DELETE;
        return $this->checkNoCondition('removeNoCondition')->run();
    }

    /**
     * Executes UPDATE query.
     * 
     * @return  int|boolean
     */
    public function update()
    {
        $this->mode = AbstractBuilder::UPDATE;
        return $this->checkValueColumns()
                        ->checkNoCondition('updateNoCondition')
                        ->run();
    }

    /**
     * Fetches record using SELECT query.
     * 
     * @param   int $fetch
     * @return  array
     */
    public function get($fetch = PDO::FETCH_OBJ)
    {
        $this->mode = AbstractBuilder::SELECT;
        $result = $this->run(false);
        $isStatement = $result instanceof PDOStatement;
        $cacheKey = $this->cacheKey;
        if ($isStatement) {
            $result = $result->fetchAll($fetch);
            $this->setResultInCache($result);
        }
        $this->reset();
        $this->isLastResultFromCache = $isStatement === false;
        $this->lastCacheKey = $cacheKey;
        return $result;
    }

    /**
     * Returns name of cache config which need to be used for storing result into cache.
     * 
     * @return string
     */
    private function getCacheName()
    {
        return Connection::connection($this->connectionName)->getCacheName();
    }

    /**
     * Stores result into cache.
     * 
     * @param mixed $result
     */
    private function setResultInCache($result)
    {
        # No cache key set then no need to set cache,
        # This cache key can also be set from database connection class if
        # cache key is prepared from executed query.
        if (!is_string($this->cacheKey)) {
            return false;
        }

        # There's no cache config set.
        if (($cacheName = $this->getCacheName()) === null) {
            return false;
        }

        return Nishchay::getCache($cacheName)
                        ->set($this->cacheKey, $result, $this->cacheExpiration);
    }

    /**
     * Return first record.
     * 
     * @param   int $fetch
     * @return  mixed
     */
    public function getOne($fetch = PDO::FETCH_OBJ)
    {
        !empty($this->limit) && $this->setLimit(1);
        return current($this->get($fetch));
    }

    /**
     * Executes query.
     * 
     * @return  \PDOStatement
     */
    private function run($reset = true)
    {
        $result = Connection::connection($this->connectionName)
                ->executeFromBuilder($this);
        $reset && $this->reset();
        return $result;
    }

    /**
     * Returns SQL Statement other than DELETE.
     * 
     * @return string
     */
    public function getSQL(bool $raw = true)
    {
        return Connection::connection($this->connectionName)
                        ->getSQL($this, $raw);
    }

    /**
     * Returns DELETE statement.
     * 
     * @return string
     */
    public function getDeleteSql()
    {
        $this->mode = AbstractBuilder::DELETE;
        return $this->getSQL();
    }

    /**
     * Returns binded placeholder name and its value.
     * 
     * @return array
     */
    public function getBinded()
    {
        return $this->binds;
    }

    /**
     * Executes query.
     * Returns array of rows in case of select statement or count of
     * affected columns.
     * 
     * @param   string      $query  Query String.
     * @param   int         $fetch  What kind of row should be returned.
     * @return  mixed       
     */
    public function execute($query, $fetch = \PDO::FETCH_OBJ)
    {
        $result = Connection::connection($this->connectionName)
                ->execute($query, $this->getBinded(), $fetch);
        if ($result instanceof PDOStatement) {
            return $result->fetchAll($fetch);
        }

        return $result;
    }

    /**
     * Quotes column.
     * 
     * @param   string $name
     * @return  string
     */
    public function quote($name)
    {
        return Connection::connection($this->connectionName)
                        ->quote($name);
    }

    /**
     * Resets properties of this class to their default value.
     */
    private function reset()
    {
        foreach (new static($this->connectionName) as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Every conditional clause WHERE,HAVING, Join condition,IN and NOT IN
     * uses this method to prepare proper conditional clause.
     * 
     * @param   string $column
     * @param   mixed $value
     * @return  string
     */
    private function getAssigneeValue($column, $value)
    {
        $column = trim($column);
        $value = ($value === NULL ? 'NULL' : $value);

        # What is at last position.
        $last = substr($column, -1, 1);

        # '#' to consider $value as column name and ~ to use $value as it is.
        if (in_array($last, [self::AS_IT_IS, self::AS_COLUMN])) {

            if ($value instanceof Query) {
                $value = $this->addQueryToThis($value);
            }

            # This will make value to be treated as column name.
            # So for this we will quote column if last character is #.
            # If last character is ~, we will use as it is.
            if ($last === self::AS_COLUMN) {
                $value = $this->quote($value);
            }

            # Removing last chracter, we don't need it.
            $column = substr($column, 0, -1);
        } else {
            if ($value instanceof Query) {
                $value = $this->addQueryToThis($value);
            } else {
                $value = $this->bindValue($value);
            }
        }
        return $this->getWithOperator(trim($column)) . $value;
    }

    /**
     * Returns left operand based on literal or operator passed with it.
     * This will add equal operator if there is none passed.
     *  
     * @param string $column
     * @return string
     */
    private function getWithOperator($column)
    {
        switch (true) {
            case substr($column, -1, 1) === self::LEFT_AS_IT_IS:
                return substr($column, 0, -1);
            case!in_array(substr($column, -1, 1), self::$conditionalOperator) && !in_array(strtolower(substr($column, -4, 4)), self::$conditionalOperator):
                return $this->quote($column) . ' = ';
            case preg_match(self::$conditionalRegex, $column, $match):
                return $this->quote(trim(substr($column, 0, strpos($column, $match[0])))) . " {$match[0]} ";
            default:
                return $column;
        }
    }

    /**
     * Binds value to placeholder parameter and return it's name.
     * 
     * @param   string $value
     * @return  string
     */
    public function bindValue($value)
    {
        $name = $this->getPlaceholderName();
        $this->binds[$name] = $value instanceof DateTime ? $value->format(DateUtility::MYSQL_DATETIME_FORMAT) : $value;
        return ":$name";
    }

    /**
     * Returns Placeholder parameter name.
     * 
     * @return string
     */
    private function getPlaceholderName()
    {
        return 'i' . $this->instanceNumber . 'b' . ($this->bindCount++);
    }

    /**
     * Prepare where condition.
     * 
     * @param   array $conditions
     * @return  string
     */
    private function prepareCondition($conditions)
    {
        $prepared = [];

        foreach ($conditions as $key => $value) {

            $match = [];
            if ($value instanceof Query || is_array($value)) {
                preg_match(self::$arrayBasedRegex, $key, $match);
            }

            if (isset($match[2])) {
                $prepared[] = $this->getArrayBasedClause($match, $value);
            } else if (is_array($value)) {
                $prepared[] = '(' . $this->prepareCondition($value) . ')';
            } else if (is_int($key) === false) {
                $prepared[] = $this->getAssigneeValue($key, $value);
            }
        }
        return $this->combineCondition($conditions, $prepared);
    }

    /**
     * Combines multiple conditions by joiner.
     * 
     * @param array $conditions
     * @param array $prepared
     * @return type
     */
    private function combineCondition($conditions, $prepared)
    {
        # Moving curstor to end of array to find wheather last value is joining
        # operator. Last value's key is int and value is string denotes that 
        # value is joining operator. If no operator present at end will take
        # AND as joining operator.
        end($conditions);
        $joining = 'AND';

        if (is_int(key($conditions)) && is_string(current($conditions))) {
            $joining = current($conditions);
        }

        return implode(' ' . $joining . ' ', $prepared);
    }

    /**
     * Adds query to this.
     * This is for sub query.
     * 
     * @param \Nishchay\Data\Query $query
     * @return type
     */
    private function addQueryToThis(Query $query)
    {
        $this->combineBind($query);
        return '(' . $query->getSql() . ')';
    }

    /**
     * Combines bind of passed query to this one.
     * 
     * @param \Nishchay\Data\Query $query
     */
    private function combineBind(Query $query)
    {
        $this->binds = array_merge($this->binds, $query->binds);
    }

    /**
     * Returns array based quote.
     * Such clause requires array like IN, NOT IN etc.
     * 
     * @param   array       $match
     * @param   array       $value
     * @return  string
     */
    private function getArrayBasedClause($match, $value)
    {
        if (substr($match[0], -1, 1) === self::LEFT_AS_IT_IS) {
            $cluse = $match[1];
        } else {
            $cluse = $this->quote($match[1]);
        }

        if (empty($value)) {
            throw new ApplicationException('Empty value for the [' . $cluse . '].', 2, null, 911088);
        }

        switch ($match[2]) {
            case self::IN_OPERATOR:
                $cluse .= ' IN (' . $this->getFormattedArrayBasedValue($value) . ')';
                break;
            case self::NOT_IN_OPERATOR:
                $cluse .= ' NOT IN (' . $this->getFormattedArrayBasedValue($value) . ')';
                break;
            case self::BETWEEN_OPERATOR:
                if (count($value) !== 2) {
                    throw new ApplicationException('Between clause requires array '
                            . 'with exact two elements.', 3, null, 911089);
                }
                $cluse .= ' BETWEEN ' . $this->bindValue($value[0]) . ' AND ' . $this->bindValue($value[1]);
            default:
                break;
        }
        return $cluse;
    }

    /**
     * Binds array and returns binded placeholder separated by comma.
     * If $value is query it returns query statement.
     * 
     * @param array $value
     * @return string
     */
    private function getFormattedArrayBasedValue($value)
    {
        if ($value instanceof Query) {
            return substr($this->addQueryToThis($value), 1, -1);
        }

        foreach ($value as $k => $v) {
            $value[$k] = $this->bindValue($v);
        }
        return implode(',', $value);
    }

    /**
     * Sets HAVING clause.
     * 
     * @param   string|array            $column
     * @param   string                  $value
     * @return  \Nishchay\Data\Query
     */
    public function setHaving($column, $value = '')
    {
        $column = is_string($column) ? [$column => $value] : $column;
        $this->having[] = $this->prepareCondition($column);
        return $this;
    }

    /**
     * Sets GROUP BY.
     * 
     * @param   string                  $groupBy
     * @return  \Nishchay\Data\Query
     */
    public function setGroupBy($groupBy)
    {
        $groupBy = is_string($groupBy) ? [$groupBy] : $groupBy;

        foreach ($groupBy as $key => $value) {
            $groupBy[$key] = $this->quoteCoulmn($value);
        }
        $this->groupBy .= (empty($this->groupBy) ? '' : ',') . implode(',', $groupBy);
        return $this;
    }

    /**
     * 
     * @param \Nishchay\Data\Query $query
     * @param type $all
     */
    public function addUnionQuery(Query $query, $all = FALSE)
    {
        $this->combineBind($query);
        $this->union[] = 'UNION ' . ($all ? 'ALL' : '') . PHP_EOL .
                $query->getSql();
    }

    /**
     * Quote column.
     * 
     * @param type $column
     * @return type
     */
    private function quoteCoulmn($column)
    {
        return substr($column, -1, 1) === self::LEFT_AS_IT_IS ?
                substr($column, 0, -1) : $this->quote($column);
    }

    /**
     * Sets ORDER  BY.
     * 
     * @param   string                  $orderBy
     * @return  \Nishchay\Data\Query
     */
    public function setOrderBy($orderBy)
    {
        $orderBy = is_string($orderBy) ? [$orderBy] : $orderBy;

        foreach ($orderBy as $key => $value) {
            $sort = 'ASC';
            if (!is_int($key)) {
                $sort = $value;
                $value = $key;
            }

            if (substr($value, 0, 1) === self::AS_IT_IS) {
                $value = substr($value, 1);
            } else {
                $value = $this->quoteCoulmn($value);
            }

            $orderBy[$key] = $value . " {$sort} ";
        }

        $this->orderBy .= (empty($this->orderBy) ? '' : ',') . implode(',', $orderBy);
        return $this;
    }

    /**
     * Returns count of total record returned.
     * 
     * @param   string $column
     * @return  int
     */
    public function count($column = '*')
    {
        $this->columns = 'COUNT(' . $this->quote($column) . ') AS total';
        return (int) current($this->get())->total;
    }

    /**
     * 
     * @param type $column
     * @param type $type
     * @return type
     */
    private function getAggregate($column, $type)
    {
        $this->columns = $type . '(' . $this->quote($column) . ') as ' .
                strtolower($type);
        return $this->getOne()->{strtolower($type)};
    }

    /**
     * Returns max of column.
     * 
     * @param type $column
     * @return type
     */
    public function max($column)
    {
        return $this->getAggregate($column, 'MAX');
    }

    /**
     * Returns max of column.
     * 
     * @param type $column
     * @return type
     */
    public function min($column)
    {
        return $this->getAggregate($column, 'MIN');
    }

    /**
     * Returns max of column.
     * 
     * @param type $column
     * @return type
     */
    public function avergae($column)
    {
        return $this->getAggregate($column, 'AVG');
    }

    /**
     * Returns sum of column.
     * 
     * @param type $column
     * @return type
     */
    public function sum($column)
    {
        return $this->getAggregate($column, 'SUM');
    }

    /**
     * Returns true if last result retrieved from cache.
     * 
     * @return boolean
     */
    public function isLastResultFromCache()
    {
        return $this->isLastResultFromCache;
    }

    /**
     * Sets Cache.
     * Pass $key = true if want to cache key to be auto created from executing query.
     * Passing $key = string will use the same key.
     * 
     * @param boolean|string $key
     * @param int $expiration
     * @return $this
     */
    public function setCache($key = true, $expiration = 0)
    {
        $this->cacheKey = $key;
        $this->cacheExpiration = $expiration;
        return $this;
    }

    /**
     * Returns last cacheKey used.
     * 
     * @return string
     */
    public function getLastCacheKey()
    {
        return $this->lastCacheKey;
    }

    /**
     * Starts transaction.
     * 
     * @return boolean
     */
    public function startTransaction()
    {
        $this->mode = 'startTransaction';
        return $this->run();
    }

    /**
     * Rollbacks transaction to start of transaction or to save point name.
     */
    public function rollback($savepoint = null)
    {
        $this->name = $savepoint;
        $this->mode = 'rollback';
        return $this->run();
    }

    /**
     * Commits transaction.
     * 
     * @return string
     */
    public function commit()
    {
        $this->mode = 'commit';
        return $this->run();
    }

}
