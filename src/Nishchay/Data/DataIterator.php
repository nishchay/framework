<?php

namespace Nishchay\Data;

use Nishchay\Exception\ApplicationException;
use ArrayIterator;
use Nishchay\Data\EntityManager;

/**
 * Data iterator class entity records.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DataIterator extends ArrayIterator
{

    /**
     * 
     * @param   array   $array
     */
    public function __construct($array)
    {
        parent::__construct($array);
    }

    /**
     * 
     * @param   mixed       $offset
     * @param   mixed       $value
     * @throws  Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new ApplicationException('Adding value to iterator not supported.', null, null, 911062);
    }

    /**
     * Returns all elements as array.
     * 
     * @param bool $withNull
     * @return array
     */
    public function getAsArray(bool $withNull = true): array
    {
        $array = [];
        foreach ($this as $row) {
            if ($row instanceof EntityManager) {
                $value = $row->getData('array', true, $withNull);
            } else {
                $value = [];
                foreach ($row as $k => $v) {
                    $value[$k] = $v->getData('array', true, $withNull);
                }
            }
            $array[] = $value;
        }

        return $array;
    }

    /**
     * Divides records based on alias.
     * 
     * @return \stdClass|$this
     */
    public function divide()
    {
        $first = $this->current();

        # Records contains only entity manager then return it directly
        if ($first instanceof EntityManager) {
            return $this;
        }

        $grouped = [];

        # Itarative over records
        foreach ($this as $row) {

            # As in a row there's multiple alias, we are here diving
            # based on alias.
            foreach ($row as $alias => $value) {
                $grouped[$alias][] = $value;
            }
        }

        $returning = new \stdClass();
        foreach ($first as $alias => $value) {
            $returning->{$alias} = new static($grouped[$alias]);
        }
        return $returning;
    }

    /**
     * Returns value of property name only as an array.
     *  
     * @param string $propertyName
     * @return array
     */
    public function column(string $propertyName): array
    {
        $returning = [];
        foreach ($this as $row) {
            $returning[] = $this->getValue($row, $propertyName);
        }

        return $returning;
    }

    /**
     * Returns unique values of given property name.
     * 
     * @param string $propertyName
     * @return array
     */
    public function unique(string $propertyName): array
    {
        $columns = $this->column($propertyName);

        return array_unique($columns);
    }

    /**
     * Filters records based on closure.
     * Returns new DataIterator of records for which closure returns TRUE.
     * 
     * @param \Closure $closure
     * @return \static
     */
    public function filter(\Closure $closure)
    {
        $returning = [];
        foreach ($this as $row) {
            if ($closure($row) === true) {
                $returning[] = $row;
            }
        }

        return new static($returning);
    }

    /**
     * Returns sum of value of given property name.
     * 
     * @param string $propertyName
     * @return mixed
     */
    public function sum(string $propertyName)
    {
        return array_sum($this->column($propertyName));
    }

    /**
     * Returns average of the value of given property name.
     * 
     * @param string $propertyName
     * @return type
     */
    public function average(string $propertyName)
    {
        return ($this->sum($propertyName) / $this->count());
    }

    /**
     * Returns value from row.
     * 
     * @param mixed $row
     * @param string $name
     * @return mixed
     * @throws ApplicationException
     */
    private function getValue($row, string $name)
    {
        foreach (explode('.', $name) as $key) {

            if (!isset($row->{$key})) {
                throw new ApplicationException('Key [' . $name . '] does'
                        . ' not exists in records.', 2, null);
            }

            $row = $row->{$key};
        }

        return $row;
    }

    /**
     * Group records based on property name.
     * 
     * @param string $propertyName
     * @return \Nishchay\Data\DataIterator
     * @throws ApplicationException
     */
    public function group(string $propertyName): array
    {
        $grouped = [];
        foreach ($this as $row) {
            $key = $this->getValue($row, $propertyName);

            if (is_scalar($key) === false) {
                throw new ApplicationException('Records can only be group'
                        . ' based on scaler value.', 1, null);
            }

            $grouped[$key][] = $row;
        }

        return $grouped;
    }

    /**
     * 
     * @param \Nishchay\Data\DataIterator $iterator
     * @param string $key
     * @param string|null $iteratorKey
     */
    public function combine(DataIterator $iterator, string $key, ?string $iteratorKey = null)
    {
        if ($iteratorKey === null) {
            $iteratorKey = $key;
        }

        $left = $this->column($key);
        $right = $iterator->column($iteratorKey);

        $returning = [];
        foreach ($left as $li => $lv) {
            $matched = new \stdClass();
            $this->assign($matched, $this[$li]);
            $isMatched = false;
            foreach ($right as $ri => $rv) {
                if ($lv === $rv) {
                    $isMatched = true;
                    $this->assign($matched, $iterator[$ri]);
                }
            }
            if ($isMatched) {
                $returning[] = $matched;
            }
        }

        return $returning;
    }

    /**
     * Assigns row to object.
     * 
     * @param type $object
     * @param EntityManager $row
     */
    private function assign($object, $row)
    {
        if ($row instanceof EntityManager) {
            $key = lcfirst($row->getEntityTable());
            $object->{$key} = $row;
        } else {
            foreach ($row as $alias => $value) {
                $object->{$alias} = $value;
            }
        }
    }

}
