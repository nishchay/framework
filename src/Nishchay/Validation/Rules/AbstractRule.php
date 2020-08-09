<?php

namespace Nishchay\Validation\Rules;

/**
 * Abstract class for rule.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractRule
{

    protected static $messeges = [];

    /**
     * Returns error message for given rule name.
     * 
     * @param string $ruleName
     * @return string
     */
    public function getMessage($ruleName)
    {
        if (array_key_exists($ruleName, static::$messeges)) {
            return static::$messeges[$ruleName];
        }

        return '{field} is not valid as per ' . $ruleName . ' validation rule.';
    }

    /**
     * Returns TRUE if message exists.
     * 
     * @param string $ruleName
     * @return bool
     */
    public function isMessageExists($ruleName): bool
    {
        return array_key_exists($ruleName, static::$messeges);
    }

    /**
     * Should return name of rule.
     */
    public abstract function getName(): string;
}
