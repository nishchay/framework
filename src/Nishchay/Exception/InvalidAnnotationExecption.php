<?php

namespace Nishchay\Exception;

/**
 * Exception class for invalid annotation.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class InvalidAnnotationExecption extends BaseException
{

    public function __construct($message = '', $classOrTraceBack = null, $method = null, $code = E_USER_ERROR, $previous = null)
    {
        parent::__construct($message, $classOrTraceBack, $method, $code, $previous);
    }

}
