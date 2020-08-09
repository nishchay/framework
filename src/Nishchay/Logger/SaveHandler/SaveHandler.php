<?php

namespace Nishchay\Logger\SaveHandler;

use Nishchay;

/**
 * Save Handler for Logger.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class SaveHandler
{

    /**
     *
     * @var \Nishchay\Logger\SaveHandler\AbstractSaveHandler 
     */
    private $saveHandler;

    /**
     * 
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * 
     */
    private function init()
    {
        $this->saveHandler = Nishchay::getSetting('logger.type') === 'db' ?
                (new DBHandler) : (new FileHandler);
        $this->saveHandler->open();
    }

    /**
     * 
     * @param type $logLine
     * @return type
     */
    public function write($type, $logLine)
    {
        return $this->saveHandler->write($type, $logLine);
    }

    /**
     * 
     */
    public function __destruct()
    {
        $this->saveHandler->close();
    }

}
