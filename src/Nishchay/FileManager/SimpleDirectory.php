<?php

namespace Nishchay\FileManager;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Nishchay\Utility\Unit as UnitUtility;
use Nishchay\Utility\StringUtility;

/**
 * SimpleDirectory class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class SimpleDirectory extends AbstractDirectory
{

    /**
     * Searchable types
     * 
     * @var array 
     */
    private $searchable = ['BOTH', 'FILE', 'DIRECTORY'];

    public function __construct($path, $create = false)
    {
        $this->open($path, $create);
    }

    /**
     * Last access time
     * 
     * @return type
     */
    public function getAccessTime()
    {
        return $this->call('fileatime', [$this->realPath]);
    }

    /**
     * Creation time
     * 
     * @return type
     */
    public function getCreationTime()
    {
        return $this->call('filectime', [$this->realPath]);
    }

    /**
     * name of directory where this directory located
     * 
     * @return string
     */
    public function directory()
    {
        $dir = $this->call('dirname', [$this->realPath]);

        /**
         * Below process just to add directory separator
         */
        $last = substr($dir, strlen($dir) - 1);

        if ($last != DS) {
            return $dir . DS;
        }

        return $dir;
    }

    /**
     * Returns all directories at root folder or sub-folder folder when $nest is TRUE
     * 
     * @param bool $nest
     * @return array
     */
    public function getDirectoris($nest = false)
    {
        return $this->fetch('is_dir', $nest);
    }

    /**
     * Returns all files at root folder or sub-folder folder when $nest is TRUE
     * 
     * @param bool $nest
     * @return array
     */
    public function getFiles($nest = false)
    {
        return $this->fetch('is_file', $nest);
    }

    /**
     * Searches file having given extension and return as an array
     * 
     * @param type $extension
     */
    public function getHavingExtension($extension, $nest = false)
    {
        $files = [];

        $this->walk(function($path, &$files, $extension) {
            if (is_file($path)) {
                if (StringUtility::getExplodeLast('.', $path) === $extension) {
                    $files[] = $path;
                }
            }
        }, array(&$files, $extension), $nest, null);

        return $files;
    }

    /**
     * Move directory to new path
     * 
     * @param type $new_path
     */
    public function move($new_path)
    {
        $this->close();
        rename($this->realPath, $new_path);
        $this->open($new_path);
    }

    /**
     * Remove directory even if it is non-empty
     * 
     */
    public function remove()
    {
        $this->close();
        $dots = RecursiveDirectoryIterator::SKIP_DOTS;
        $child_first = RecursiveIteratorIterator::CHILD_FIRST;
        $root_iterator = new RecursiveDirectoryIterator($this->realPath, $dots);
        $iterator = new RecursiveIteratorIterator($root_iterator, $child_first);

        foreach ($iterator as $it) {
            $path = $it->getRealPath();

            if ($it->isDir()) {
                rmdir($path);
            } else {
                unlink($path);
            }
        }

        rmdir($this->realPath);
    }

    /**
     * Rename directory to new name
     * 
     * @param string $new_name
     */
    public function rename($new_name)
    {
        $new_name = $this->directory() . $new_name;
        $this->close();
        rename($this->realPath, $new_name);
        $this->open($new_name);
    }

    /**
     * 
     * @param type $search
     * @param type $type
     * @throws InvalidArgumentException
     */
    public function getMatching($search, $type = 'BOTH')
    {
        $search = preg_quote($search, '#');
        $type = strtoupper($type);
        $files = array();

        if (!in_array($type, $this->searchable)) {
            throw new InvalidArgumentException('Type should be any of [' . implode(',', $this->searchable) . '].', null, null, 917005);
        }

        $this->walk(function($path, $type, $search, &$files) {
            switch ($type) {
                case 'BOTH':
                    $basename = basename($path);
                    break;
                case 'FILE':
                    if (is_file($path)) {
                        $basename = basename($path);
                    }
                    break;
                case 'DIRECTORY':
                    if (is_dir($path)) {
                        $basename = basename($path);
                    }
                    break;
            }
            if (preg_match('#(.*?)' . $search . '(.*?)#is', $basename)) {
                $files[] = $path;
            }
        }, array($type, $search, &$files), true, null);

        return $files;
    }

    /**
     * Returns size of directory in default or given form
     * 
     * @param int $precision
     * @param string $format
     * @param boolean $unit
     * @return float
     */
    public function getSize($precision, $format = 'bytes', $unit = false)
    {

        $size = 0;

        $this->walk(function($path, &$size) {
            if (!is_dir($path)) {
                $size += filesize($path);
            }
        }, array(&$size), true, null);

        return UnitUtility::memory($size, $precision, $unit, $format);
    }

}
