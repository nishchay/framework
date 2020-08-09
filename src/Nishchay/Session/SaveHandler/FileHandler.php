<?php

namespace Nishchay\Session\SaveHandler;

use Nishchay\Exception\ApplicationException;
use Nishchay\FileManager\SimpleFile;

/**
 * File handler for session.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class FileHandler extends AbstractSaveHandler
{

    /**
     * Instance of SimpleFile pointing to session file.
     * 
     * @var \Nishchay\FileManager\SimpleFile 
     */
    private $file;

    /**
     * Path to session storage directory.
     * 
     * @var string 
     */
    private $storagePath;

    /**
     * 
     * @param string $storagePath
     */
    public function __construct($storagePath)
    {
        $this->setStoragePath($storagePath);
    }

    /**
     * Sets storage path.
     * 
     * @param string $storagePath
     */
    private function setStoragePath($storagePath)
    {
        if (!file_exists($storagePath)) {
            throw new ApplicationException('Session storage path [' . $storagePath
                    . '] does not exist.', null, null, 929003);
        }

        $this->storagePath = $storagePath;
    }

    /**
     * 
     * @return boolean
     */
    public function close()
    {
        return TRUE;
    }

    /**
     * Removes session data file of given session ID.
     * 
     * @param string $session_id
     * @return boolean
     */
    public function destroy($session_id)
    {
        $this->openFile($this->getPath($session_id));
        return $this->file->remove();
    }

    /**
     * 
     * @param type $maxlifetime
     * @return boolean
     */
    public function gc($maxlifetime)
    {
        foreach (glob($this->storagePath) as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return TRUE;
    }

    /**
     * 
     * @param type $save_path
     * @param type $name
     * @return boolean
     */
    public function open($save_path, $session_name)
    {
        return TRUE;
    }

    /**
     * Opens session storage file.
     * 
     * @param string $path
     */
    private function openFile($path)
    {
        $this->file = new SimpleFile($path, SimpleFile::START_READ_WRITE);
    }

    /**
     * Reads persisted session data of given session ID from file.
     * 
     * @param string $session_id
     * @return string
     */
    public function read($session_id)
    {
        $this->openFile($this->getPath($session_id));
        $content = $this->file->read();
        $this->file->close();
        return $content;
    }

    /**
     * Returns session storage path.
     * 
     * @param string $session_id
     * @return string
     */
    private function getPath($session_id)
    {
        return $this->storagePath . DS . $session_id . '.txt';
    }

    /**
     * Writes session data to file of given session ID.
     * 
     * @param string $session_id
     * @param string $session_data
     * @return boolean
     */
    public function write($session_id, $session_data)
    {
        $this->openFile($this->getPath($session_id));
        $this->file->truncateWrite($session_data);
        $this->file->close();
        return TRUE;
    }

}
