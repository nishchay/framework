<?php

namespace Nishchay\Handler;

use Nishchay;
use Nishchay\Utility\StringUtility;

/**
 * Exception detail class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Detail
{

    /**
     * Exception message.
     * 
     * @var string 
     */
    private $message;

    /**
     * File name.
     * 
     * @var stirng 
     */
    private $file;

    /**
     * Line number.
     * 
     * @var int 
     */
    private $line;

    /**
     * Error code.
     * 
     * @var int 
     */
    private $code;

    /**
     *
     * @var string 
     */
    private $type;

    /**
     *
     * @var string
     */
    private $actualType;

    /**
     *
     * @var type 
     */
    private $trace;

    /**
     * 
     * @param   int         $code
     * @param   string      $message
     * @param   string      $file
     * @param   int         $line
     */
    public function __construct($code, $message, $file, $line, $type, $trace)
    {
        $this->code = $code;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        $this->actualType = $type;
        $this->type = StringUtility::getExplodeLast('\\', $type);
        $this->trace = $trace;
    }

    /**
     * Returns true if error reporting is off.
     * 
     * @return type
     */
    public function isShowable()
    {
        # For live stage we will never ask to display any kind error message.
        if (Nishchay::isApplicationStageLive()) {
            return false;
        }

        # Error show config.
        $show = Nishchay::getSetting('error.show.' .
                        Nishchay::getApplicationStage());

        # When all is not null and its boolean we will returns directly its
        # for all type of errors.
        if (isset($show->all) && $show->all !== null && is_bool($show->all)) {
            return $show->all;
        }

        $type = strtolower($this->getType());
        return isset($show->{$type}) ? ($show->{$type} === true) :
                (isset($show->other) ? $show->other : true);
    }

    /**
     * Returns exception message.
     * Return default error message when $supress = true.
     * 
     * @return string
     */
    public function getMessage()
    {
        if ($this->isShowable() === false) {
            return Nishchay::getSetting('error.suppressedMessage');
        }
        return $this->getActualMessage();
    }

    /**
     * Returns actual exception message.
     * 
     * @return string
     */
    public function getActualMessage()
    {
        return $this->message;
    }

    /**
     * Returns file name in which exception occurred.
     * 
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Returns line number where exception occurred.
     * 
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Return error code number.
     * 
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns type of error.
     * 
     * @return type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns type of error.
     * 
     * @return type
     */
    public function getActualType()
    {
        return $this->actualType;
    }

    /**
     * Returns call trace.
     * 
     * @return type
     */
    public function getTrace()
    {
        return $this->trace;
    }

}
