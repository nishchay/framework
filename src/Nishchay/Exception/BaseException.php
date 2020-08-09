<?php

namespace Nishchay\Exception;

use Exception;
use ReflectionClass;
use ReflectionMethod;

/**
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class BaseException extends Exception
{

    /**
     *
     * @var type 
     */
    public $custom = 'show';

    /**
     * 
     * @param   string  $message
     * @param   string|int  $classOrTraceBack This can be class name trace back number. We will go back to this number for find class and method
     * @param   string  $method
     * @param   int $code
     * @param   Throwable   $previous
     */
    public function __construct($message = '', $classOrTraceBack = null, $method = null, $code = E_USER_ERROR, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->init($classOrTraceBack, $method);
    }

    /**
     * Sets file and line based on $class and $method or back trace.
     * 
     * @param string $classOrTraceBack
     * @param string $method
     */
    private function init($classOrTraceBack, $method)
    {

        if (($reflection = $this->getReflection($classOrTraceBack, $method)) !== false) {
            $this->file = $reflection->getFileName();
            $this->line = $reflection->getStartLine();
        } else if (is_int($classOrTraceBack)) {
            $trace = $this->getTrace();
            $at = $classOrTraceBack - 1;
            if (array_key_exists($at, $trace) && array_key_exists('file', $trace[$at])) {
                $this->file = $trace[$at]['file'];
                $this->line = $trace[$at]['line'];
            }
        }
    }

    /**
     * Returns instance of ReflectionClass or ReflectionMethod based on $class
     * and $method.
     * 
     * @param string $class
     * @param string $method
     * @return boolean|ReflectionClass|ReflectionMethod
     */
    private function getReflection($class, $method)
    {
        if ($class !== null && $method !== null) {
            return new ReflectionMethod($class, $method);
        } else if ($method === null && ($class !== null && is_int($class) === false)) {
            return new ReflectionClass($class);
        }

        return false;
    }

}
