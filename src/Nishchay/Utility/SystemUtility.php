<?php

namespace Nishchay\Utility;

/**
 * Description of System
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
final class SystemUtility
{

    /**
     * Replaces directory separator to system specific directory separator.
     * 
     * @param string $path
     * @return string
     */
    public static function refactorDS($path)
    {
        return str_replace(['/', '\\'], DS, $path);
    }

    /**
     * Replaces directory separator to system specific directory separator.
     * 
     * @param string $path
     * @return srting
     */
    public static function refactorDSReference(&$path)
    {
        return $path = self::refactorDS($path);
    }

    /**
     * Creates directory if does not exist.
     * 
     * @param string $path
     */
    public static function resolvePath($path, $mode = 0777)
    {
        self::refactorDSReference($path);

        if (file_exists($path)) {
            return $path;
        }

        # Finding root directory
        $exploded = explode(DS, $path);
        $newPath = current($exploded);

        # We don't want first element
        array_shift($exploded);
        foreach ($exploded as $value) {
            $newPath .= DS . $value;
            if (file_exists($newPath)) {
                continue;
            }
            mkdir($newPath, $mode, true);
        }
        return $path;
    }

    /**
     * Returns environment value if set or default as specified in second argument.
     * 
     * @param string $name
     * @param mixed $default
     */
    public static function getEnvironmentValue($name, $default = false)
    {
        return getenv($name) !== false ? getenv($name) : $default;
    }

    /**
     * Returns setting of given name from php.ini.
     * 
     * @param string $name
     * @return string
     */
    public static function iniGet(string $name)
    {
        return ini_get($name);
    }

    /**
     * Updates php.ini setting of $key to $value.
     * 
     * @param string $key
     * @param mixed $value
     * @return string
     */
    public static function iniSet(string $key, $value)
    {
        return ini_set($key, $value);
    }

    /**
     * Updates maximum execution time.
     * 
     * @param int $time
     */
    public static function timeLimit(int $time)
    {
        self::iniSet('set_time_limit', $time);
        self::iniSet('max_execution_time', $time);

        if (function_exists('set_time_limit')) {
            set_time_limit($time);
        }
    }

    /**
     * Updates memory limit.
     * 
     * @param string $limit
     * @return type
     */
    public static function memoryLimit(string $limit)
    {
        return self::iniSet('memory_limit', $limit);
    }

}
