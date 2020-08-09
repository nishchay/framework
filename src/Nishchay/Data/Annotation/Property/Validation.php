<?php

namespace Nishchay\Data\Annotation\Property;

use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * Validation annotation class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Validation extends BaseAnnotationDefinition
{

    use MethodInvokerTrait;

    /**
     * Callback method name.
     * 
     * @var string
     */
    private $callback = false;

    /**
     * 
     * @param type $class
     * @param type $method
     * @param type $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $this->setter($parameter, 'parameter');
    }

    /**
     * Returns callback function.
     * 
     * @return array
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Sets callback parameter.
     * 
     * @param strinng $callback
     */
    protected function setCallback($callback)
    {
        $expl = explode('::', $callback);
        $class = count($expl) > 1 ? $expl[0] : $this->class;
        $method = count($expl) > 1 ? $expl[1] : $expl[0];

        if ($this->isCallbackExist([$class, $method]) === false) {
            throw new InvalidAnnotationExecption('Validation callback method ['
                    . $class . '::' . $method . '] does not exists.',
                    $this->class, null, 911028);
        }
        $this->callback = ['class' => $class, 'method' => $method];
    }

}
