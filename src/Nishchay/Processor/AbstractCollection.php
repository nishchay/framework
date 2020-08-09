<?php

namespace Nishchay\Processor;

use Nishchay\Exception\NotSupportedException;

/**
 * Abstract collection class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractCollection
{

    /**
     * Flag for collection is enabled or disabled.
     * 
     * @var boolean 
     */
    private static $storing = true;

    /**
     * Closes storing.
     */
    public static function close()
    {
        self::$storing = false;
    }

    /**
     * Checks storing is closed or not.
     * 
     * @throws \Nishchay\Exception\NotSupportedException
     */
    protected function checkStoring()
    {
        if (self::$storing === false) {
            throw new NotSupportedException('Collection has been closed.', null, null, 925023);
        }
    }

    /**
     * Returns number of elements in collection.
     * 
     * @return int
     */
    public abstract function count();
}
