<?php

namespace Nishchay\Event\Annotation\Method;

use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Exception\InvalidAnnotationParameterException;
use Nishchay\Processor\Names;

/**
 * Event Intended annotation
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Intended extends BaseAnnotationDefinition
{

    /**
     * Annotation parameters.
     * 
     * @var array 
     */
    private $parameter = [];

    /**
     * Type of event.
     * 
     * @var string 
     */
    private $type;

    /**
     * Name of scope or context based on type of event.
     * Not applicable for global events.
     * 
     * @var boolean|string 
     */
    private $name = false;

    /**
     * Initialization.
     * 
     * @param   string  $class
     * @param   string  $method
     * @param   array   $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $this->init($parameter);
    }

    /**
     * Initialization.
     * 
     * @param array $parameter
     */
    private function init($parameter)
    {
        $this->parameter = $parameter;
        $this->type = Names::TYPE_GLOBAL;
        $this->setter($parameter, 'parameter');
        $this->parameter = null;
    }

    /**
     * Returns type for which event is defined.
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets type of event.
     * 
     * @param string $type
     */
    protected function setType(string $type)
    {
        $type = strtolower($type);

        if (!in_array($type, [Names::TYPE_GLOBAL, Names::TYPE_CONTEXT, Names::TYPE_SCOPE])) {
            throw new InvalidAnnotationParameterException('Event type [' . $type . ']'
                    . ' not supported.', $this->class, $this->method, 916002);
        }

        # Name parameter is required for event other than global
        if (in_array($type, [Names::TYPE_SCOPE, Names::TYPE_CONTEXT]) && array_key_exists('name', $this->parameter) === false) {
            throw new InvalidAnnotationExecption('Annotation [intended] parameter name [name] parameter is required'
                    . ' when event type is [' . $type . '].', $this->class, $this->method, 916003);
        }
        $this->type = $type;
    }

    /**
     * Returns name of the class or file depends on the value of type.
     * 
     * @return boolean|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets class or file name for which event is defined.
     * Applicable only if event intended for is single.
     * 
     * @param string $name
     */
    protected function setName(string $name)
    {
        if (empty($name)) {
            throw new InvalidAnnotationParameterException('Annotation [intended] parameter name'
                    . ' [name] should not be empty.', $this->class, $this->method, 916004);
        }
        $this->name = $name;
    }

}
