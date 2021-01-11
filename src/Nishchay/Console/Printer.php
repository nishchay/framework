<?php

namespace Nishchay\Console;

/**
 * Description of Printer
 *
 * @author bpatel
 */
class Printer
{

    /**
     * Green color code.
     * 
     * @var string
     */
    const GREEN_COLOR = '0;32';

    /**
     * Yellow color code.
     * 
     * @var string
     */
    const YELLOW_COLOR = '1;33';

    /**
     * Grey color code.
     * 
     * @var string
     */
    const GREY_COLOR = '0;90';

    /**
     * Red color code.
     */
    const RED_COLOR = '0;31';

    /**
     * Writes string on console with color if specified.
     * 
     * @param string $string
     * @param string|boolean $color
     * @return null
     */
    public static function write($string, $color = false, $code = 0)
    {
        if (!empty($code)) {
            $string = '(' . $code . ')' . $string;
        }
        if ($color === false) {
            echo $string;
            return;
        }
        echo static::getColorText($string, $color);

        return;
    }

    /**
     * Writes string in color red.
     * 
     * @param string $string
     * @param int $code
     * @return null
     */
    public static function red($string, $code = 0)
    {
        return self::write($string, self::RED_COLOR, $code);
    }

    /**
     * Writes string in color red.
     * 
     * @param string $string
     * @param int $code
     * @return null
     */
    public static function grey($string, $code = 0)
    {
        return self::write($string, self::GREY_COLOR, $code);
    }

    /**
     * Writes string in color red.
     * 
     * @param string $string
     * @param int $code
     * @return null
     */
    public static function green($string, $code = 0)
    {
        return self::write($string, self::GREEN_COLOR, $code);
    }

    /**
     * Writes string in color red.
     * 
     * @param string $string
     * @param int $code
     * @return null
     */
    public static function yellow($string, $code = 0)
    {
        return self::write($string, self::YELLOW_COLOR, $code);
    }

    /**
     * Returns text wrapped with color code.
     * 
     * @param stirng $string
     * @param string $color
     * @return string
     */
    public static function getColorText($string, $color)
    {
        return "\033[{$color}m{$string}\033[0m";
    }

}
