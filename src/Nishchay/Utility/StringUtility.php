<?php

namespace Nishchay\Utility;

use Nishchay\Exception\NotSupportedException;

/**
 * Description of StringUtility
 *
 * @author Pratik
 */
class StringUtility
{

    /**
     * Converts string camel case.
     * 
     * @param   string      $string
     * @return  string
     */
    public static function toCamelCase($string)
    {
        return preg_replace_callback(['/(\b|_)([a-z])/', '/(\s|_)/'], function($match) {
            return strtoupper($match[2]);
        }, $string);
    }

    /**
     * Explodes string by given literal and returns last element from it.
     * 
     * @param   mixed   $literal
     * @param   string  $string
     * @return  mixed
     */
    public static function getExplodeLast($literal, $string)
    {
        $array = explode($literal, $string);
        return end($array);
    }

    /**
     * Explodes string by given literal and return first element from it.
     * 
     * @param   mixed   $literal
     * @param   string  $string
     * @return  mixed
     */
    public static function getExplodeFirst($literal, $string)
    {
        $array = explode($literal, $string);
        return current($array);
    }
    
    /**
     * Explodes string.
     * 
     * @param type $literal
     * @param type $string
     * @return type
     */
    public static function explode($literal, $string)
    {
        $array = explode($literal, $string);
        return $array;
    }

    /**
     * Removes given string[$remove] from end of string and returns it.
     * If $remove does not found in $string and then it returns as it is.
     * 
     * @param type $remove
     * @param type $string
     * @return type
     */
    public static function removeFromEnd($remove, $string)
    {
        if (($position = strrpos($string, $remove)) === FALSE) {
            return $string;
        }
        return substr($string, 0, $position);
    }

    /**
     * Swaps variable value.
     * 
     * @param string $string1
     * @param string $string2
     * @return boolean
     */
    public static function swap(&$string1, &$string2)
    {
        if (is_string($string1) === false || is_string($string2) === false) {
            throw new NotSupportedException(__METHOD__ . ' expects parameter to'
            . ' be string');
        }
        $temp = $string1;
        $string1 = $string2;
        $string2 = $temp;
        return TRUE;
    }

    /**
     * Generates random string of given length.
     * 
     * @param   int         $length
     * @return  string
     */
    public static function getRandomString($length, $charOnly = false)
    {
        $string = 'abcdefghijklmnopqrstuvwxyz0123456789-_@$:#ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($charOnly) {
            $string = preg_replace('#([^a-zA-Z0-9])#', '', $string);
        }
        $random = '';
        $strlen = strlen($string) - 1;
        for ($i = 1; $i <= $length; $i++) {
            $random .= $string[mt_rand(0, $strlen)];
        }
        return $random;
    }

    /**
     * Escapes HTML special characters.
     * 
     * @param string $string
     * @return string
     */
    public static function htmlEscape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, FALSE);
    }

}
