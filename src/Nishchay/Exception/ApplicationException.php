<?php

namespace Nishchay\Exception;

/**
 * Exception class for application related errors.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ApplicationException extends BaseException
{

    /**
     * Calls parent constructor.
     * 
     * @param string $message
     * @param srting $classOrTraceBack
     * @param string $method
     * @param int $code
     * @param Throwable $previous
     */
    public function __construct($message = '', $classOrTraceBack = NULL, $method = NULL, $code = E_USER_ERROR, $previous = NULL)
    {
        parent::__construct($message, $classOrTraceBack, $method, $code, $previous);
    }

}
