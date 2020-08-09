<?php

namespace Nishchay\Handler\Annotation;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Processor\Names;

/**
 * Handler Annotation class.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Handler extends BaseAnnotationDefinition
{

    /**
     * Tpye parameter value.
     * 
     * @var string 
     */
    private $type;

    /**
     * Name parameter value.
     * 
     * @var string 
     */
    private $name;

    /**
     * List of parameter defined.
     * 
     * @var array 
     */
    private $parameter;

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $parameter
     */
    public function __construct($class, $parameter)
    {
        parent::__construct($class, null);
        $this->parameter = $parameter;
        ;
        $this->setter($parameter, 'parameter');
    }

    /**
     * Returns type parameter value.
     * 
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns name parameter value.
     * 
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets type parameter value.
     * 
     * @param   string    $type
     */
    public function setType($type)
    {
        if (!in_array(strtolower($type), [
                    Names::TYPE_CONTEXT,
                    Names::TYPE_GLOBAL,
                    Names::TYPE_SCOPE
                ])) {
            throw new NotSupportedException('Handler type ' . $type .
                    ' not supported', $this->class);
        }

        $type = strtolower($type);

        if (in_array($type, [Names::TYPE_SCOPE, Names::TYPE_CONTEXT]) && array_key_exists('name', $this->parameter) === false) {
            throw new InvalidAnnotationExecption('Parameter [name] required when'
                    . ' handler type is scope.', $this->class, null, 919001);
        }
        $this->type = $type;
    }

    /**
     * Sets name parameter value.
     * 
     * @param   string    $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

}
