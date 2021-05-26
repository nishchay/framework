<?php

namespace Nishchay\Attributes\Entity\Property;

use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Attributes\AttributeTrait;
use Nishchay\Attributes\Entity\Property\DataType;

/**
 * Description of Derived
 *
 * @author bhavik
 */
#[\Attribute]
class Derived
{

    use AttributeTrait {
        verify as parentVerify;
    }

    /**
     * Attribute name.
     */
    const NAME = 'derived';

    /**
     * Property name on which annotation is defined.
     * 
     * @var string 
     */
    private $propertyName;

    /**
     * Derived type. can be
     * callback,join_from,join.
     * 
     * @var string 
     */
    private $derivedType;

    /**
     * Data type of derived value.
     * 
     * @var DataType
     */
    private $dataType;

    /**
     * 
     * @param string|null $from
     * @param string|array $join
     * @param string|null $callback
     * @param string|null $type
     * @param string $hold
     * @param string|null $group
     */
    public function __construct(private ?string $from = null,
            private string|array $join = [], private ?string $callback = null,
            private ?string $type = null, private string $hold = 'single',
            private array $group = [], private array $property = [])
    {
        ;
    }
    
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * 
     */
    public function verify()
    {
        $this->parentVerify();
        $this->refactorJoin()
                ->refactorFrom()
                ->refactorCallback()
                ->refactorHold();
    }

    /**
     * Sets property name
     * @param string $propertyName
     */
    public function setPropertyName(string $propertyName)
    {
        if ($this->propertyName !== null) {
            return null;
        }

        $this->propertyName = $propertyName;
    }

    /**
     * Refactors join parameter.
     * 
     * @return $this
     * @throws InvalidAttributeException
     */
    private function refactorJoin()
    {
        if (empty($this->join) || is_array($this->join)) {
            return $this;
        }
        $this->derivedType = 'join';

        if (method_exists($this->class, $this->join) === false) {
            throw new InvalidAttributeException('Join callback [' .
                            $this->class . '::' . $this->join . '] does exist for property [' .
                            $this->class . '::' . $this->propertyName . '].',
                            $this->class, null, 911017);
        }


        $this->join = call_user_func([new ($this->class), $this->join]);

        return $this;
    }

    /**
     * Refactors from parameter.
     * 
     * @return $this
     */
    private function refactorFrom()
    {
        if ($this->from === null) {
            return $this;
        }

        $this->derivedType = 'joinFrom';

        return $this;
    }

    /**
     * Refactors from parameter.
     * 
     * @return $this
     */
    private function refactorCallback()
    {
        if (empty($this->callback)) {
            return $this;
        }
        if (method_exists($this->class, $this->callback) === false) {
            throw new InvalidAttributeException('Callback [' .
                            $this->callback . '] for derived property [' . $this->class .
                            '::' . $this->propertyName . ' ] does not exist.',
                            $this->class, null, 911018);
        }
        $this->derivedType = 'callback';

        return $this;
    }

    private function refactorHold()
    {

        $allowedHold = [
            'single' => 'string',
            'multiple' => 'array'
        ];

        if (!array_key_exists($this->hold, $allowedHold)) {
            throw new InvalidAttributeException('Invalid hold type'
                            . ' for property [' . $this->class . '::' .
                            $this->propertyName . '].', $this->class, null,
                            911020);
        }
        $this->hold = $allowedHold[$this->hold];
    }

    /**
     * Returns true if property is deriving from property which is relative
     * to another entity.
     * 
     * @return tybooleanpe
     */
    public function isFrom()
    {
        return $this->derivedType === 'joinFrom';
    }

    /**
     * Returns true if property is derived using callback.
     * 
     * @return boolean
     */
    public function isCallback()
    {
        return $this->derivedType === 'callback';
    }

    /**
     * Returns true if property is derived using custom join.
     * 
     * @return boolean
     */
    public function isJoin()
    {
        return $this->derivedType === 'join';
    }

    /**
     * Registers data type of deriving property.
     * 
     * @param string $propertyName
     * @param string $dataType
     * @return boolean
     */
    public function registerDataType($propertyName, DataType $dataType)
    {
        if ($propertyName === null) {
            $this->dataType = $dataType;
            return true;
        }
        $this->dataType[$propertyName] = $dataType;
        return true;
    }

    /**
     * Returns data type of deriving property.
     * 
     * @return DataType
     */
    public function getDataType(string $propertyName = null)
    {
        return $propertyName === null ?
                $this->dataType : $this->dataType[$propertyName];
    }

}
