<?php

namespace Nishchay\Logger;

use Nishchay\Logger\SaveHandler\SaveHandler;

/**
 * Logger class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Logger
{

    /**
     *
     * @var \Nishchay\Logger\SaveHandler\SaveHandler 
     */
    private $saveHandler;

    /**
     * 
     */
    public function __construct()
    {
        $this->saveHandler = new SaveHandler;
    }

    /**
     * 
     * @param type $logLine
     * @return type
     */
    public function info(string $logLine)
    {
        return $this->write('info', $logLine);
    }

    /**
     * 
     * @param type $logLine
     * @return type
     */
    public function warning(string $logLine)
    {
        return $this->write('warning', $logLine);
    }

    /**
     * 
     * @param type $logLine
     * @return type
     */
    public function error(string $logLine)
    {
        return $this->write('error', $logLine);
    }

    /**
     * 
     * @param type $logLine
     * @return type
     */
    public function notice(string $logLine)
    {
        return $this->write('notice', $logLine);
    }

    /**
     * 
     * @param type $type
     * @param type $logLine
     * @return type
     */
    public function write(string $type, string $logLine)
    {
        return $this->saveHandler->write($type, $logLine);
    }

}
