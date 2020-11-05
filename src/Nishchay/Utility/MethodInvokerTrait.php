<?php

namespace Nishchay\Utility;

use Nishchay\Exception\ApplicationException;

/**
 * Description of MethodInvokerTrait
 *
 * @author bpatel
 */
trait MethodInvokerTrait
{

    /**
     * Executes callback.
     * 
     * @param string|array $method    should be in class::method or
     *                                  [class,method] format or closure.
     * @param array $parameter          Optional. Should be array.
     * @return mixed
     */
    private function invokeMethod($method, array $parameter = [])
    {
        if ($method instanceof \Closure) {
            return call_user_func_array($method, $parameter);
        }

        if (is_string($method)) {
            $method = explode('::', $method);
        }

        if (is_array($method)) {
            if (!isset($method[0]) || !isset($method[1])) {
                throw new ApplicationException('When first argument for method'
                        . __METHOD__ . 'is array it should contain class or object as'
                        . ' first element and method name being second element.');
            }
            $method[0] = is_string($method[0]) ? new $method[0] : $method[0];
        }

        return call_user_func_array($method, $parameter);
    }

    /**
     * Returns TRUE if callback exist.
     * 
     * @param   string  $callback
     * @return  boolean
     */
    private function isCallbackExist($callback)
    {
        if (is_string($callback)) {
            $callback = explode('::', $callback);
        }

        return (isset($callback[0]) && isset($callback[1]) &&
                method_exists($callback[0], $callback[1]));
    }

}
