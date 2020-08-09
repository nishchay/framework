<?php

namespace Nishchay\Controller\Annotation\Method;

use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Annotation\BaseAnnotationDefinition;

/**
 * Doc annotation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Doc extends BaseAnnotationDefinition
{

    /**
     * Lists of parameters.
     * 
     * @var array
     */
    private $parameter;

    /**
     *
     * @var type 
     */
    private $parameterName;

    /**
     *
     * @var     array 
     */
    private $annotation;

    /**
     * 
     * @param   string  $class
     * @param   string  $method
     * @param   array   $parameterName
     * @param   array   $parameter
     */
    public function __construct($class, $method, $parameterName, $parameter)
    {
        parent::__construct($class, $method);

        if ($parameter === false) {
            throw new InvalidAnnotationExecption('Parameters missing for annotation @doc_' . $parameterName, $this->class, $this->method, 914011);
        }

        $this->parameterName = $parameterName;
        $this->parameter = $parameter;
        $this->setAnnotation();
    }

    /**
     * Returns parameter array for which this doc annotation is defined.
     * 
     * @return array
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Returns annotation name for which this doc annotation is defined.
     * 
     * @return  array
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }

    /**
     * 
     * @throws InvalidAnnotationExecption
     */
    protected function setAnnotation()
    {
        if (!array_key_exists('annotation', $this->parameter) || !is_string($this->parameter['annotation'])) {
            throw new InvalidAnnotationExecption('Annotation [doc] requires paramter name [annotation].', $this->class, $this->method, 914012);
        }

        $this->annotation = strtolower($this->parameter['annotation']);

        #Removing annotation name.
        array_shift($this->parameter);
    }

}
