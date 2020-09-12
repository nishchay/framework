<?php

namespace Nishchay\Processor\Loader;

use Nishchay\Exception\ApplicationException;
use Nishchay\FileManager\SimpleDirectory;
use Nishchay\Utility\Coding;

/**
 * Configuration Loader.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Loader
{

    /**
     * Loaded configs.
     * 
     * @var array 
     */
    private $loaded = [];

    /**
     * Returns configuration of given configuration name.
     * 
     * @param   string          $name
     * @return  \stdClass
     */
    public function getConfig($name)
    {
        # Already been loaded?
        if (array_key_exists($name, $this->loaded)) {
            return $this->loaded[$name];
        }

        $file = CONFIG . $name . '.php';
        if (!file_exists($file)) {
            throw new ApplicationException('Setting file [' . $file . '] does not exist.', null, null, 925002);
        }

        $config = require $file;
        return $this->loaded[$name] = $this->toObject($config);
    }
    
    /**
     * Returns array converted to object.
     * 
     * @param array $array
     * @return \stdClass
     */
    public function toObject($array)
    {
        return is_array($array) ? (object) array_map(__METHOD__, $array) : $array;
    }

    /**
     * Requires every file of given directory.
     * 
     * @param string $dir
     */
    public function deepRequired($dir)
    {
        $directory = new SimpleDirectory($dir);
        foreach ($directory->getFiles(true) as $path) {
            # Ignoring files which starts with underscore.
            if (strpos(basename($path), '_') === 0) {
                continue;
            }

            if (file_exists($path)) {
                require $path;
            }
        }
    }

    /**
     * Returns value of config.
     * 
     * @param type $name
     * @return boolean
     */
    public function getValue($name)
    {
        $array = explode('.', $name);
        $return = $this->getConfig($array[0]);
        if ($return === false) {
            return false;
        }

        array_shift($array);

        foreach ($array as $value) {
            # Because if $return->$value=NULL, below returns FASLE.
            # We will have to then check by converting $return to array.
            if (!isset($return->$value)) {
                if (array_key_exists($value, (array) $return)) {
                    return $return->$value;
                }
                return false;
            }

            $return = $return->$value;
        }

        return is_object($return) ? (clone $return) : $return;
    }

}
