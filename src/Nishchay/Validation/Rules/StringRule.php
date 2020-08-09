<?php

namespace Nishchay\Validation\Rules;

/**
 * String validation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class StringRule extends AbstractRule
{

    /**
     * String rule name.
     */
    const NAME = 'string';

    /**
     *
     * @var array
     */
    protected static $messeges = [
        'min' => '{field} should be atleast {0} character long.',
        'max' => '{field} should not be greater then {0} character.',
        'range' => 'Character length of {field} should be between {0} and {1}.',
        'exact' => '{field} must be {0} character long',
        'alpha' => '{field} should be alphabatic only.',
        'alphaspace' => '{field} should be alphabate with space.',
        'alphanum' => '{field} should be alphanumeric.',
        'alphanumspace' => '{field} should be alphanumeric with space.',
        'startswith' => '{field} should starts with {0}.',
        'endswith' => '{field} should ends with {0}.',
        'lowercase' => '{field} should be in lowercase only.',
        'uppercase' => '{field} should be in uppercase only.',
        'json' => '{field} should be JSON'
    ];

    /**
     * Returns true if character length of $value is greater or equal to
     * $length.
     * 
     * @param   string      $value
     * @param   int         $length
     * @return  boolean
     */
    public function min(string $value, int $length): bool
    {
        return mb_strlen(trim($value)) >= $length;
    }

    /**
     * Returns true if character length of $value is less then or equal to
     * $length.
     *  
     * @param   string      $value
     * @param   int         $length
     * @return  boolean
     */
    public function max(string $value, int $length): bool
    {
        return mb_strlen(trim($value)) <= $length;
    }

    /**
     * Returns true if character length of $value is between $min and $max.
     * This also returns false when $min or $max parameter is not numeric or 
     * $min greater then $max.
     * 
     * @param   string      $value
     * @param   int         $min
     * @param   int         $max
     * @return  boolean
     */
    public function range(string $value, int $min, int $max): bool
    {
        if ($min > $max) {
            return false;
        }
        $length = mb_strlen(trim($value));
        return $length >= $min && $length <= $max;
    }

    /**
     * Returns true if $value contains only alphabet character.
     * 
     * @param string $value
     * @return boolean
     */
    public function alpha(string $value): bool
    {
        return preg_match('#^([\p{L}]+)$#ui', $value);
    }

    /**
     * Returns true if $value contains only alphabet character and space.
     * 
     * @param string $value
     * @return boolean
     */
    public function alphaspace(string $value): bool
    {
        return preg_match('#^([\p{L}\s]+)$#ui', $value);
    }

    /**
     * Returns true if $value contains only alpha numeric character.
     * 
     * @param string $value
     * @return boolean
     */
    public function alphanum(string $value): bool
    {
        return preg_match('#^([\p{L}0-9]+)$#ui', $value);
    }

    /**
     * Returns true if $value contains only alpha numeric character, space and
     * number.
     * 
     * @param string $value
     * @return boolean
     */
    public function alphanumspace(string $value): bool
    {
        return preg_match('#^([\p{L}\s0-9]+)$#ui', $value);
    }

    /**
     * Returns true if $value starts with $with.
     * 
     * @param string $value
     * @param string $with
     * @return boolean
     */
    public function startswith(string $value, string $with): bool
    {
        return mb_strpos($value, $with, 0, mb_detect_encoding($value)) === 0;
    }

    /**
     * Returns true if $value ends with $with.
     * 
     * @param string $value
     * @param string $with
     * @return boolean
     */
    public function endswith(string $value, string $with): bool
    {
        $encoding = mb_detect_encoding($value);
        return mb_strrpos($value, $with, 0, $encoding) === (mb_strlen($value, $encoding) - mb_strlen($with, $encoding));
    }

    /**
     * Returns true if $value is in lowercase.
     * 
     * @param string $value
     * @return boolean
     */
    public function lowercase(string $value): bool
    {
        return $value === mb_strtolower($value, mb_detect_encoding($value));
    }

    /**
     * Returns true if $value is in uppercase.
     * 
     * @param string $value
     * @return boolean
     */
    public function uppercase(string $value): bool
    {
        return $value === mb_strtoupper($value, mb_detect_encoding($value));
    }

    /**
     * Returns true if $value is JSON.
     * 
     * @param string $value
     * @return bool
     */
    public function json(string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        json_decode($value);

        return json_last_error() !== JSON_ERROR_NONE;
    }

    /**
     * Returns name of validation type.
     * 
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

}
