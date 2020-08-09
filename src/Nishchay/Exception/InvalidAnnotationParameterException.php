<?php

namespace Nishchay\Exception;

/**
 * Exception class for invalid annotation parameter.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class InvalidAnnotationParameterException extends BaseException
{

    /**
     * 
     * @param type $message
     * @param type $classOrTraceBack
     * @param type $method
     * @param type $code
     * @param type $previous
     */
    public function __construct($message = '', $classOrTraceBack = null, $method = null, $code = E_USER_ERROR, $previous = null)
    {
        parent::__construct($message, $classOrTraceBack, $method, $code, $previous);
    }

}
