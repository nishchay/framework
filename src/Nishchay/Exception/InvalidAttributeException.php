<?php

namespace Nishchay\Exception;

/**
 * Exception class for invalid attribute.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
class InvalidAttributeException extends BaseException
{

    public function __construct($message = '', $classOrTraceBack = null,
            $method = null, $code = E_USER_ERROR, $previous = null)
    {
        parent::__construct($message, $classOrTraceBack, $method, $code,
                $previous);
    }

}
