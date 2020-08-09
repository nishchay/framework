<?php

namespace Nishchay\Data;

use Nishchay\Exception\ApplicationException;
use ArrayIterator;

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
     * @return type
     */
    public function getAsArray(bool $withNull = true)
    {
        $array = [];
        foreach ($this as $row) {
            $array[] = $row->getData('array', true, $withNull);
        }

        return $array;
    }

}
