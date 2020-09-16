<?php

namespace Nishchay\Utility;

use \SimpleXMLElement;

/**
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ArrayUtility
{

    /**
     * 
     * @param   array     $array
     * @param   mixed     $key
     * @param   string    $sortOrder
     */
    public static function multiSort(&$array, $key, $sortOrder = 'asc')
    {
        $toSort = [];
        foreach ($array as $k => $v) {
            $toSort[$k] = $v[$key];
        }
        $sortOrder === 'asc' ? asort($toSort) : arsort($toSort);
        $sorted = [];
        foreach ($toSort as $k => $v) {
            $sorted[$k] = $array[$k];
        }
        return $array = $sorted;
    }

    /**
     * Remove one element form start and end of array.
     * 
     * @param array $array
     */
    public static function compact(&$array)
    {
        array_shift($array);
        array_pop($array);
    }

    /**
     * Sorts an array by key.
     * 
     * @param array $mainArray
     * @param string $sortby
     * @return array
     */
    public static function customeKeySort($mainArray, $sortby)
    {
        $sorted = [];
        foreach ($sortby as $name) {
            if (array_key_exists($name, $mainArray)) {
                $sorted[$name] = $mainArray[$name];
                unset($mainArray[$name]);
            }
        }
        return array_merge($sorted, $mainArray);
    }

    /**
     * Return key position within array starting from zero.
     * Returns FALSE if key not found.
     * 
     * @param string $findKey
     * @param array $array
     * @return boolean|int
     */
    public static function getKeyPosition($findKey, $array)
    {
        $position = 0;
        foreach (array_keys($array) as $key) {
            if ($key == $findKey) {
                return $position;
            }
            $position++;
        }
        return FALSE;
    }

    /**
     * Converts Array to XML.
     * 
     * @param type      $array
     * @param boolean   $object
     */
    public static function toXML($array, $object = false, $root = 'root')
    {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><{$root}></{$root}>");

        function arrayToXML($array, SimpleXMLElement &$xml)
        {
            foreach ($array as $key => $value) {
                if (is_numeric($key)) {
                    $key = "element{$key}";
                }
                if (is_array($value)) {
                    if ($key === '@attributes') {
                        foreach ($value as $attrName => $attrValue) {
                            $xml->addAttribute($attrName, $attrValue);
                        }
                        continue;
                    }
                    $val = array_key_exists('@value', $value) ? $value['@value'] : null;
                    if (array_key_exists('@name', $value)) {
                        $key = $value['@name'];
                    }
                    $node = $xml->addChild($key, $val);
                    arrayToXML($value, $node);
                } else if (strpos($key, '@') === false) {
                    $xml->addChild($key, htmlspecialchars($value));
                }
            }
        }

        arrayToXML($array, $xml);
        return $object ? $xml : $xml->asXML();
    }

    /**
     * Returns TRUE if $key exist in array.
     * 
     * @param type $key
     * @param type $array
     * @param type $case
     * @return boolean
     */
    public static function isExist($key, $array, $case = true)
    {
        if ($case) {
            return array_key_exists($key, $array);
        }

        foreach ($array as $index => $value) {
            if (strtolower($index) === strtolower($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns TRUE if $value exist in array.
     * 
     * @param type $value
     * @param type $array
     * @param type $case
     * @return boolean
     */
    public static function in($value, $array, $case = true)
    {
        if ($case) {
            return in_array($value, $array);
        }

        foreach ($array as $key => $val) {
            if (strtolower($val) === strtolower($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Removes first element from array.
     * 
     * @param array $array
     * @return array
     */
    public static function removeFirst($array)
    {
        array_shift($array);
        return $array;
    }

    /**
     * Returns array converted to object.
     * 
     * @param array $array
     * @return \stdClass
     */
    public static function toObject($array)
    {
        $isIndexedArray = self::isIndexedArray($array);
        $object = $isIndexedArray ? [] : (new \stdClass());
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = self::toObject($value);
            }
            $isIndexedArray ? ($object[$key] = $value) : ($object->{$key} = $value);
        }
        return $object;
    }

    /**
     * 
     * @param array $array
     * @return boolean
     */
    public static function isIndexedArray(array $array)
    {
        if ([] === $array) {
            return true;
        }
        return array_keys($array) === range(0, count($array) - 1);
    }

}
