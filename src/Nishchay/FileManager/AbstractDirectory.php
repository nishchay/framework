<?php

namespace Nishchay\FileManager;

use Nishchay\Exception\ApplicationException;
use Nishchay\Utility\SystemUtility as SystemUtility;
use Nishchay\Utility\Coding;
use Nishchay\Utility\StringUtility;

/**
 * Abstract class for directory.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractDirectory
{

    /**
     * Path to directory.
     * @var stirng 
     */
    protected $path;

    /**
     * Real path to directory.
     * 
     * @var string 
     */
    protected $realPath;

    /**
     * Handler to directory.
     * 
     * @var object 
     */
    protected $handler;

    /**
     * Call php built in file function
     * 
     * @param   string      $name
     * @param   array       $param
     * @return  mixed
     */
    protected function call($name, $param)
    {
        $this->isHandlerExist();

        # Calling actual function
        return call_user_func_array($name, $param);
    }

    /**
     * Throws exception if handler does not created or destroyed
     */
    protected function isHandlerExist()
    {
        if ($this->handler === null) {
            throw new ApplicationException('Handler on directory not created or destroyed.', null, null, 917001);
        }
    }

    /**
     * Common method to list all files or direcotry located at root
     * OR nesting directory when $nest is TRUE
     * 
     * @param function $callback
     * @param boolean $nest
     * @return array
     */
    protected function fetch($callback, $nest = false)
    {
        $files = [];

        # Using walker method
        $this->walk(function($path, $callback, &$files) {
            if (call_user_func($callback, $path)) {
                $files[] = $path;
            }
        }, [$callback, &$files], $nest);

        return $files;
    }

    /**
     * Closes current open directory
     */
    public function close()
    {
        $this->call("closedir", [$this->handler]);

        # After closing directory we are setting directory handler to NULL
        $this->handler = null;
    }

    /**
     * 
     * @param type $path
     * @param type $create
     */
    public function open($path, $create = false)
    {
        SystemUtility::refactorDSReference($path);
        $this->path = Coding::fileLookUp($path);
        if ($this->path === FALSE) {
            if (!$create) {
                throw new ApplicationException('Invalid path [' . $path . '].', null, null, 917002);
            } else {
                if (!file_exists(StringUtility::getExplodeFirst(DS, $path) . DS)) {
                    $this->path = RESOURCES . $path;
                } else {
                    $this->path = $path;
                }
                SystemUtility::resolvePath($this->path);
            }
        }
        $this->handler = opendir($this->path);
        $this->realPath = realpath($this->path);
    }

    /**
     * apply callback function to all files and directory
     * 
     * @param   \Closure        $callback
     * @param   array           $callback_args
     * @param   boolean         $nest
     * @param   string          $path
     */
    public function walk($callback, $callback_args, $nest = true, $path = null)
    {
        if ($path === null) {
            $path = $this->realPath;
        }

        $dir = opendir($path);

        while (FALSE !== ($current = readdir($dir))) {
            $current_path = $path . DS . $current;

            if ($current !== "." && $current !== "..") {
                if (is_callable($callback) && is_array($callback_args)) {
                    #Passing path as first parameter in callback
                    array_unshift($callback_args, $current_path);
                    call_user_func_array($callback, $callback_args);
                    array_shift($callback_args);
                }

                # Walking under sub directory only if $nest is TRUE.
                if ($nest) {
                    if (file_exists($current_path) && is_dir($current_path)) {
                        $this->walk($callback, $callback_args, $nest, $current_path);
                    }
                }
            }
        }
        closedir($dir);
    }

    /**
     * Last access time
     */
    abstract function getAccessTime();

    /**
     * Create time
     */
    abstract function getCreationTime();

    /**
     * Fetches all directory located at root of given directory 
     * if $nest is TRUE,returns all directory to the end of all directories
     */
    abstract function getDirectoris($nes = false);

    /**
     * Fetches all files located in given directory 
     * if $nest is TRUE,returns files to the end of all directories
     */
    abstract function getFiles($nest = false);

    /**
     * List all files having given exntension
     */
    abstract function getHavingExtension($extension);

    /**
     * Move diretory to new location
     */
    abstract function move($new_path);

    /**
     * Rename directory 
     */
    abstract function rename($new_name);

    /**
     * Remove directory even if it is non-empty
     */
    abstract function remove();

    /**
     * Search files,directory or both based given $type
     */
    abstract function getMatching($key, $type = 'BOTH');

    /**
     * Returns size of array
     */
    abstract function getSize($precision, $format = 'bytes', $unit = false);
}
