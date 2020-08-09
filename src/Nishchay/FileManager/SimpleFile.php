<?php

namespace Nishchay\FileManager;

use Nishchay\Utility\Unit as UnitUtility;

/**
 * SimpleFile class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class SimpleFile extends AbstractFile
{

    /**
     * 
     * @param string $path
     * @param string $mode
     */
    public function __construct($path, $mode = self::READ)
    {
        parent::open($path, $mode);
    }

    /**
     * Last access time
     * 
     * @return int
     */
    public function accessTime()
    {
        return $this->call('fileatime', [$this->realPath]);
    }

    /**
     * Creation time
     * 
     * @return string
     */
    public function createTime()
    {
        return $this->call('filectime', [$this->realPath]);
    }

    /**
     * name of directory where this file located
     * 
     * @return string
     */
    public function directory()
    {
        $dir = $this->call('dirname', [$this->realPath]);

        # Below process just to add directory separator
        $last = substr($dir, strlen($dir) - 1);

        if ($last != DS) {
            return $dir . DS;
        }

        return $dir;
    }

    /**
     * File extension
     * 
     * @return string
     */
    public function extension()
    {
        return $this->call('Coding::stringExplodeLast', ['.', $this->path]);
    }

    /**
     * move pointer to current position + specified offset
     *  
     * @param int $offset
     * @return string
     */
    public function forward($offset)
    {
        return $this->call('fseek', [$this->handler, $offset, SEEK_CUR]);
    }

    /**
     * Sets file pointer at specified offset
     * 
     * @param int $offset
     * @return string
     */
    public function go($offset)
    {
        return $this->call('fseek', [$this->handler, $offset, SEEK_SET]);
    }

    /**
     * 
     * @param type $option
     * @return type
     */
    public function getLines($option = FILE_TEXT)
    {
        return $this->call('file', [$this->realPath, $option]);
    }

    /**
     * Move file from one location to another location
     * 
     * @param string $new_path
     */
    public function move($new_path)
    {
        $this->close();
        rename($this->realPath, $new_path);
        $this->open($new_path, $this->mode);
    }

    /**
     * Reads line from staring point to ending point and returns as array.
     * 
     * @param   int         $starting_from
     * @param   int         $ending_to
     * @return  array
     */
    public function getLineArray($starting_from, $ending_to)
    {
        $array = [];
        for ($index = $starting_from; $index <= $ending_to; $index++) {
            $line = $this->getLine($index);
            if ($line === false) {
                return $array;
            }
            $array[] = $line;
        }
        return $array;
    }

    /**
     * Returns file name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->call('basename', [$this->realPath]);
    }

    /**
     * Returns Current pointer position
     * 
     * @return int
     */
    public function getPosition()
    {
        return $this->call('ftell', [$this->handler]);
    }

    /**
     * 
     * @param   int         $line
     * @return  string
     */
    public function getLine($line)
    {
        $this->splFileHandler();
        $this->handlerSpl->seek($line - 1);
        if ($this->handlerSpl->eof()) {
            return false;
        }
        return trim($this->handlerSpl->current(), PHP_EOL);
    }

    /**
     * Real path from the root
     * 
     * @return string
     */
    public function getRealpath()
    {
        $this->isHandlerExist();
        return $this->realPath;
    }

    /**
     * Remove file physically
     * 
     * @return boolean
     */
    public function remove()
    {
        $this->close();
        return unlink($this->realPath);
    }

    /**
     * read all content of file
     * 
     * @return string
     */
    public function read()
    {
        $position = $this->getPosition();
        rewind($this->handler);
        $content = $this->getRemainingContent();
        $this->go($position);

        return $content;
    }

    /**
     * Returns remaining file content from current pointer position
     * 
     * @return string|mixed
     */
    public function getRemainingContent()
    {
        return $this->call('stream_get_contents', [$this->handler]);
    }

    /**
     * Rename file 
     * 
     * @param string $new_name
     */
    public function rename($new_name)
    {
        $new_name = $this->directory() . $new_name . '.' . $this->extension();
        $this->close();
        rename($this->realPath, $new_name);
        $this->open($new_name, $this->mode);
    }

    /**
     * Put file pointer at beginning of file
     * 
     * @return boolean
     */
    public function reset()
    {
        return $this->call('rewind', [$this->handler]);
    }

    /**
     * File size
     * 
     * @return string
     */
    public function getSize($precision = 2, $format = 'bytes', $unit = false)
    {
        $size = $this->call('filesize', [$this->realPath]);
        return UnitUtility::memory($size, $precision, $unit, $format);
    }

    /**
     * writes content starting from pointer
     * 
     * @param string $content
     * @return boolean
     */
    public function write($content)
    {
        $this->isHandlerExist();
        if (flock($this->handler, LOCK_EX)) {
            fwrite($this->handler, $content);
            fflush($this->handler);
            flock($this->handler, LOCK_UN);
            return true;
        }
        return false;
    }

    /**
     * Truncates file content and then write content from start.
     * 
     * @param string $content
     * @return boolean
     */
    public function truncateWrite($content)
    {
        # We will first store current mode of file which is opened and its path.
        # Then we will close current opened file, once its closed we then
        # opens in 'w' mode so that the content file gets removed.
        $mode = $this->mode;
        $path = $this->getRealpath();

        # Closing current opened file.
        $this->close();

        # Opening file in 'w' mode so that content of this file gets removed.
        $this->open($path, static::TRUNCATE_WRITE);

        # Here doing our main job, writing content to file from starting.
        $this->write($content);

        # Now we should change mode of the file in which file was actully 
        # opened but only if actual mode is not 'w'.
        if (static::TRUNCATE_WRITE !== $mode) {
            $this->close();
            $this->open($path, $mode);
        }

        return true;
    }

}
