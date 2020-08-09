<?php

namespace Nishchay\Data\Annotation\Method;

use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Data\Annotation\Trigger\AfterChange;
use Nishchay\Data\Annotation\Trigger\BeforeChange;

/**
 * Annotation defined on entity method.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class EntityMethod extends BaseAnnotationDefinition
{

    /**
     * After change annotation.
     * 
     * @var array
     */
    private $afterchange = [];

    /**
     * Bfore change annotation.
     * 
     * @var array
     */
    private $beforechange = [];

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $annotation
     */
    public function __construct($class, $method, $annotation)
    {
        parent::__construct($class, $method);
        $this->setter($annotation);
    }

    /**
     * Returns after change annotation.
     * 
     * @return array
     */
    public function getAfterchange()
    {
        return $this->afterchange;
    }

    /**
     * Returns before change annotation.
     * 
     * @return array
     */
    public function getBeforechange()
    {
        return $this->beforechange;
    }

    /**
     * Sets after change trigger.
     * 
     * @param array $afterchange
     */
    protected function setAfterchange($afterchange)
    {
        empty($afterchange) && $afterchange[0] = [];
        foreach ($afterchange as $parameter) {
            $this->afterchange[] = new AfterChange($this->class, $this->method, $parameter);
        }
    }

    /**
     * Sets before change trigger.
     *  
     * @param array $beforechange
     */
    protected function setBeforechange($beforechange)
    {
        empty($beforechange) && $beforechange[0] = [];
        foreach ($beforechange as $parameter) {
            $this->beforechange[] = new BeforeChange($this->class, $this->method, $parameter);
        }
    }

}
