<?php

namespace Nishchay\Utility;

/**
 * Description of Unit
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
final class Unit
{

    /**
     * 
     * @param   int         $size
     * @param   int         $precision
     * @param   boolean     $unit
     * @param   string      $format
     * @return  double
     */
    public static function memory(int $size, int $precision = 2, bool $unit = false, ?string $format = null)
    {
        $conv = ['bytes', 'KB', 'MB', 'GB', 'TB'];

        if ($format !== NULL)
        {
            $key = array_search(strtoupper($format), $conv);
            if ($key !== FALSE)
            {
                $value = round($size / pow(1024, $key), $precision) . ' ' . ($unit ? $conv[$key] : "");
                return trim($value);
            }
        }

        if ($size == 0)
        {
            $value = '0 ' . ($unit ? $conv[0] : "");
            return trim($value);
        }
        $base = log($size) / log(1024);
        if (!isset($conv[floor($base)]))
        {
            $value = $size . ' ' . ($unit ? $conv[0] : "");
            return trim($value);
        }
        $value = round(pow(1024, $base - floor($base)), $precision) . ' ' . ($unit ? $conv[floor($base)] : "");
        return trim($value);
    }

}
