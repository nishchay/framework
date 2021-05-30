<?php

namespace Nishchay\Data;

use Nishchay\Attributes\AttributeTrait;
use Nishchay\Attributes\Entity\Event\{
    AfterChange,
    BeforeChange
};

/**
 * attribute defined on entity method.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class EntityMethod
{

    use AttributeTrait;

    /**
     * After change attribute.
     * 
     * @var array
     */
    private $afterChange = [];

    /**
     * Bfore change attribute.
     * 
     * @var array
     */
    private $beforeChange = [];

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $attributes
     */
    public function __construct(string $class, string $method, array $attributes)
    {
        $this->setClass($class)
                ->setMethod($method);
        $this->processAttributes($attributes);
    }

    /**
     * Returns after change attribute.
     * 
     * @return array
     */
    public function getAfterChange(): array
    {
        return $this->afterChange;
    }

    /**
     * Returns before change attribute.
     * 
     * @return array
     */
    public function getBeforeChange(): array
    {
        return $this->beforeChange;
    }

    /**
     * Sets after change trigger.
     * 
     * @param BeforeChange $afterChange
     */
    protected function setAfterChange(AfterChange $afterChange)
    {
        $this->afterChange[] = $afterChange;
    }

    /**
     * Sets before change trigger.
     *  
     * @param BeforeChange $beforeChange
     */
    protected function setBeforeChange(BeforeChange $beforeChange)
    {
        $this->beforeChange[] = $beforeChange;
    }

}
