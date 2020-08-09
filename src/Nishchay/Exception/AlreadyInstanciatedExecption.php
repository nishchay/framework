<?php

namespace Nishchay\Exception;

use Nishchay\Exception\BaseException;

/**
 * If more than one instance created for singleton class
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class AlreadyInstanciatedExecption extends BaseException
{

    public function __construct($message = '', $classOrTraceBack = null, $method = null, $code = E_USER_ERROR, $previous = null)
    {
        parent::__construct($message, $classOrTraceBack, $method, $code, $previous);
    }

}
