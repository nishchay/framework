<?php

namespace Nishchay\Exception;

use Nishchay\Exception\BaseException;

/**
 * InvalidConsoleCommandException
 *
 * @author bpatel
 */
class InvalidConsoleCommandException extends BaseException
{

    public function __construct($message = '', $classOrTraceBack = null, $method = null, $code = E_USER_ERROR, $previous = null)
    {
        parent::__construct($message, $classOrTraceBack, $method, $code, $previous);
    }

}
