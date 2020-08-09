<?php

namespace Nishchay\Controller\Annotation\Method;

use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Exception\InvalidAnnotationParameterException;

/**
 * Controller annotation
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Priority extends BaseAnnotationDefinition
{

    /**
     * Parameters defined on annotation.
     * 
     * @var array 
     */
    private $parameter = FALSE;

    /**
     * Value of priority.
     * 
     * @var int 
     */
    private $value = FALSE;

    /**
     * 
     * @param   stirng  $class
     * @param   string  $method
     * @param   array   $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $this->parameter = $parameter;
        $this->setter($parameter, 'parameter');
    }

    /**
     * Returns priority value.
     * 
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets value defined in annotation.
     * 
     * @param int $value
     */
    protected function setValue($value)
    {
        if (is_numeric($value) === false) {
            throw new InvalidAnnotationParameterException('Annotation [priority]'
                    . ' parameter name [value] must be numeric.', $this->class, $this->method, 914017);
        }

        $this->value = (int) $value;
    }

}
