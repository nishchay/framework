<?php

namespace Nishchay\FileManager;

use Nishchay\Exception\ApplicationException;
use Exception;
use Nishchay\Utility\SystemUtility as SystemUtility;
use Nishchay\Utility\Coding;
use Nishchay\Utility\StringUtility;
use SplFileObject;

/**
 * Abstract class for SimpleFile utility class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractFile
{

    /**
     * File Pointer at beginning for reading only
     */
    const READ = 'r';

    /**
     * File Pointer at beginning for reading and writing
     */
    const READ_WRITE = 'r+';

    /**
     * Writing Purpose only setting file pointer at beginning and removes all content within
     * If File does not found it creates
     */
    const TRUNCATE_WRITE = 'w';

    /**
     * Removes Content from file and puts file pointer at beginning to reading and writing
     * If File does not exist it creates
     */
    const TRUNCATE_READ_WRITE = 'w+';

    /**
     * Open file for writing purpose only and puts file pointer at end of file
     * If File does not exist it creates
     */
    const END_WRITE = 'a';

    /**
     * For reading and writing.Sets file pointer at end of file. 
     * If file does not exist it creates 
     */
    const END_READ_WRITE = 'a+';

    /**
     * For writing only. Sets file pointer at start of file.
     * IF file does not exist it creates.
     */
    const START_WRITE = 'c';

    /**
     * For reading and writing only. Sets file pointer at start of file.
     * IF file does not exist it creates.
     */
    const START_READ_WRITE = 'c+';

    /**
     * File Path
     * 
     * @var string 
     */
    protected $path = null;

    /**
     * Resolved File Path
     * 
     * @var string 
     */
    protected $realPath = null;

    /**
     * File Pointer of opened file
     * 
     * @var object 
     */
    protected $handler = null;

    /**
     *
     * @var object
     */
    protected $handlerSpl = null;

    /**
     * Mode in which function does not try to create new file if it does not exist
     * 
     * @var array 
     */
    protected $nonCreateMode = [self::READ, self::READ_WRITE];

    /**
     * File Mode
     * 
     * @var string 
     */
    protected $mode;

    /**
     * Call built in file function
     * 
     * @param string $name
     * @param array $param
     * @return mixed
     */
    public function __call($name, $param)
    {
        $this->isHandlerExist();

        //_calling actual function
        return call_user_func_array($name, $param);
    }

    protected function call($name, $param)
    {
        return call_user_func_array($name, $param);
    }

    /**
     * throws exception if file to pointer on this object does not exist
     * if pointer exist does not return anything
     * @return null
     */
    protected function isHandlerExist()
    {
        if ($this->handler == null) {
            throw new ApplicationException('Pointer to file destroyed or not created.', null, null, 917003);
        }
    }

    /**
     * 
     */
    protected function splFileHandler()
    {
        if ($this->handlerSpl === null) {
            $this->handlerSpl = new SplFileObject($this->realPath);
        }
    }

    /**
     * Closes Opened File and sets pointer to NULL
     * Further access to any file after file closes throws an exception
     */
    public function close()
    {
        $this->call('fclose', [$this->handler]);

        # After closing file we are setting file pointer to NULL
        $this->handler = null;
    }

    /**
     * If handler does not exist(i.e file not opened) throws an exception
     * Most method of this class use at first line of method to check for handler exist
     * 
     * @return null
     */
    public function open($path, $mode = self::READ)
    {

        SystemUtility::refactorDSReference($path);

        $this->mode = $mode;

        $this->path = Coding::fileLookUp($path);

        if ($this->path === FALSE) {
            if (!file_exists(StringUtility::getExplodeFirst(DS, $path) . DS)) {
                $this->path = RESOURCES . $path;
            } else {
                $this->path = $path;
            }

            //getting inside
            while (true) {
                break;
            }

            # Sstill not found? now we are giving up 
            # and throwing exception if mode is File::READ or File::READ_WRITE
            if (in_array($mode, $this->nonCreateMode)) {
                throw new Exception($path . ' does not exist');
            }
        }

        if (is_dir($this->path)) {
            throw new Exception('SimpleFile class work with only regular file. Use SimpleDirectory class for working with directory', null, null, 917004);
        }



        # Create path if does not exist
        SystemUtility::resolvePath(dirname($this->path));

        # Opening
        $this->handler = fopen($this->path, $this->mode);

        # Getting real path
        $this->realPath = realpath($this->path);
    }

    /**
     * Returns pointer to file
     * 
     * @return int
     */
    public function handler()
    {
        $this->isHandlerExist();

        return $this->handler;
    }

    /**
     * Last access time
     */
    abstract function accessTime();

    /**
     * Creation time
     */
    abstract function createTime();

    /**
     * name of directory where this file located
     */
    abstract function directory();

    /**
     * File extension
     */
    abstract function extension();

    /**
     * move pointer to current position + specified offset
     */
    abstract function forward($offset);

    /**
     * Sets file pointer at specified offset
     */
    abstract function go($offset);

    /**
     * Return File content into array exploded by new line
     */
    abstract function getLines($option = FILE_TEXT);

    /**
     * Move file from one location to another location
     */
    abstract function move($path);

    /**
     * Returns file name
     */
    abstract function getName();

    /**
     * Returns Current pointer position
     */
    abstract function getPosition();

    /**
     * Real path from the root
     */
    abstract function getRealpath();

    /**
     * Remove file physically
     * 
     * @return null
     */
    abstract function remove();

    /**
     * read all content of file
     */
    abstract function read();

    /**
     * Returns remaining file content from current pointer position
     */
    abstract function getRemainingContent();

    /**
     * Rename file 
     */
    abstract function rename($new_name);

    /**
     * Puts pointer at beginning of file
     */
    abstract function reset();

    /**
     * Returns size of opened file
     */
    abstract function getSize($precision = 2, $format = 'bytes', $unit = false);

    /**
     * Writes content
     */
    abstract function write($content);
}
