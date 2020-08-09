<?php

namespace Nishchay\Exception;

/**
 * Exception class for request method not allowed.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class RequestMethodNotAllowedException extends BaseException
{

    const STATUS_CODE = 405;

    /**
     * Calls parent constructor.
     * 
     * @param string $message
     * @param srting $classOrTraceBack
     * @param string $method
     * @param int $code
     * @param Throwable $previous
     */
    public function __construct($message = '', $classOrTraceBack = null, $method = null,
            $code = E_USER_ERROR, $previous = null)
    {
        parent::__construct($message, $classOrTraceBack, $method, $code, $previous);
    }

}
