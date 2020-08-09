<?php

namespace Nishchay\Controller\Annotation\Method\Parameter;

use Processor;
use Nishchay\Exception\InvalidAnnotationParameterException;
use Nishchay\Exception\ApplicationException;
use Nishchay\Annotation\BaseAnnotationDefinition;

/**
 * Segment annotation definition class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Segment extends BaseAnnotationDefinition
{

    /**
     * index defined in annotation.
     * 
     * @var string|int 
     */
    private $index = FALSE;

    /**
     * Segment value.
     * 
     * @var string 
     */
    private $segmentValue = FALSE;

    /**
     * 
     * @param   stirng $class
     * @param   string $method
     * @param   array $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $this->paramter = $parameter;
        $this->setter($parameter, 'parameter');
    }

    /**
     * Returns index defined in annotation.
     * 
     * @return string|index
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Sets index defined in annotation then processes request to find and set segment value.
     * 
     * @param   string|int $index
     * @throws  InvalidAnnotationParameterException
     * @throws  ApplicationException
     */
    protected function setIndex($index)
    {

        if (!is_string($index) && !is_integer($index)) {
            throw new InvalidAnnotationParameterException('Annotation [segment]'
                    . ' parameter name [index] should be string or int.', $this->class,
                    $this->method, 914010);
        }

        $this->index = $index;

        $urlParts = Processor::getStageDetail('urlParts');
        $segment = Processor::getStageDetail('segment');

        if (empty($index)) {
            return $this->segmentValue = $segment;
        }

        #When index or key does not exist.
        if (!isset($segment[$index]) && !isset($urlParts[$index])) {
            $this->segmentValue = false;
        } else {
            if (array_key_exists($index, $urlParts)) {
                $this->segmentValue = $urlParts[$index];
            } else {
                $this->segmentValue = $segment[$index];
            }
        }
    }

    /**
     * Returns segment value.
     * 
     * @return string
     */
    public function getSegmentValue()
    {
        return $this->segmentValue;
    }

}
