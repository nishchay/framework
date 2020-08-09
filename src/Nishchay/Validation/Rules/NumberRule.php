<?php

namespace Nishchay\Validation\Rules;

/**
 * Number validation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class NumberRule extends AbstractRule
{

    /**
     * Number rule name.
     */
    const NAME = 'number';

    /**
     * Error messages.
     * 
     * @var array
     */
    protected static $messeges = [
        'number' => '{field} is not number.',
        'max' => '{field} should be greater then {0}.',
        'min' => '{field} should be less then {0}.',
        'range' => '{field} should be between {0} and {1}.',
        'odd' => '{field} should be odd number.',
        'even' => '{field} should be even number.',
        'negative' => '{field} should be negative number.',
        'positive' => '{field} should be positive number.'
    ];

    /**
     * Returns true if $value is numeric.
     * 
     * @param   string $value
     * @return  boolean
     */
    public function number($value): bool
    {
        return is_numeric($value);
    }

    /**
     * Returns true if $value is less or equal to $max.
     * 
     * @param   string      $value
     * @param   int         $max
     * @return  boolean
     */
    public function max($value, $max): bool
    {
        return is_numeric($value) && is_numeric($max) && $value <= $max;
    }

    /**
     * Returns true if $value is greater or equal to $min.
     * 
     * @param   string      $value
     * @param   int         $min
     * @return  boolean
     */
    public function min($value, $min): bool
    {
        return is_numeric($value) && is_numeric($min) && $value >= $min;
    }

    /**
     * Returns true if $value is in between $min and $max.
     * 
     * @param   string      $value
     * @param   int         $min
     * @param   int         $max
     * @return  boolean
     */
    public function range($value, $min, $max): bool
    {
        if (is_numeric($value) === false || is_numeric($min) === false || is_numeric($max) === false) {
            return false;
        }
        return $value >= $min && $value <= $max;
    }

    /**
     * Returns true if $value is even.
     * 
     * @param   int $value
     * @return  boolean
     */
    public function even($value): bool
    {
        return is_numeric($value) && (int) $value % 2 === 0;
    }

    /**
     * Returns true if $value is odd.
     * 
     * @param   int     $value
     * @return  boolean
     */
    public function odd($value): bool
    {
        return is_numeric($value) && (int) $value % 2 !== 0;
    }

    /**
     * Returns true if $value is negative.
     * 
     * @param int $value
     * @return boolean
     */
    public function negative($value): bool
    {
        return is_numeric($value) && (int) $value < 0;
    }

    /**
     * Returns true if $value is positive.
     * 
     * @param int $value
     * @return boolean
     */
    public function positive($value): bool
    {
        return is_numeric($value) && (int) $value > 0;
    }

    /**
     * Returns name of validation type.
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

}
