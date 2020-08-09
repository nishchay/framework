<?php

namespace Nishchay\Logger\SaveHandler;

use Nishchay;
use Nishchay\FileManager\SimpleFile;

/**
 * File save Handler for Logger.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class FileHandler extends AbstractSaveHandler
{

    /**
     * Path to logger file.
     * 
     * @var string 
     */
    private $file;

    /**
     * Flag for whether file exists or not.
     * @var type 
     */
    private $isFileExists = false;

    /**
     * Just returns false.
     * 
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Creates file if does not exist.
     * 
     */
    public function open()
    {
        $this->file = Nishchay::getSetting('logger.path') . DS .
                $this->getName(Nishchay::getSetting('logger.duration')) . '.txt';
    }

    /**
     * Creates file if does not exists.
     * 
     * @return boolean
     */
    public function initFile()
    {
        if ($this->isFileExists === true) {
            return true;
        }
        if (file_exists($this->file) === false) {
            $file = new SimpleFile($this->file, SimpleFile::END_READ_WRITE);
            $file->close();
        }

        return $this->isFileExists = true;
    }

    /**
     * Writes log to file.
     * 
     * @param string $type
     * @param string $logLine
     */
    public function write(string $type, string $logLine)
    {
        $this->initFile();
        file_put_contents($this->file, '[' . strtoupper($type) . '] ' . $logLine . PHP_EOL, FILE_APPEND);
    }

}
