<?php

namespace Nishchay\Exception;

/**
 * Exception class for unauthorized access to thigs.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class UnAuthorizationException extends BaseException
{

    /**
     * HTTP header status code.
     */
    const STATUS_CODE = 401;

    public function __construct($message = '', $classOrTraceBack = null, $method = null, $code = E_USER_ERROR, $previous = null)
    {
        parent::__construct($message, $classOrTraceBack, $method, $code, $previous);
    }

}
