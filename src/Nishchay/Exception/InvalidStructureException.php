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
     * @param string|int|null $classOrTraceBack Not used, kept so that it accepts same arguments as other exceptions.
     * @param string|null $method Not used.
     * @param int $code
     * @param Throwable $previous
     */
    public function __construct($message, $classOrTraceBack = null,
            $method = null, $code = 0, $previous = null)
    {
        parent::__construct($message, (int) $code, $previous);
        $this->line = 0;
    }

}
