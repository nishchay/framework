<?php

namespace Nishchay\Exception;

/**
 * Exception class invalid structure.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
use Exception;

class InvalidStructureException extends Exception
{

    public $custom = TRUE;

    /**
     * Calls parent constructor.
     * 
     * @param string $message
     * @param int $code
     * @param Throwable $previous
     */
    public function __construct($message, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->line = null;
    }

}
