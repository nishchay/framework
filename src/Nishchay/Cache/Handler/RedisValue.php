<?php

namespace Nishchay\Cache\Handler;

/**
 * Redis cache handler.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class RedisValue
{

    /**
     * Redis item value.
     * 
     * @var mixed
     */
    private $value;

    /**
     * Initialization.
     * 
     * @param mixd $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    /**
     * Returns value.
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

}
