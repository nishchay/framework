<?php

namespace Nishchay\Attributes\Entity\Property;

use Nishchay;
use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Attributes\AttributeTrait;
use Nishchay\Data\Query;

/**
 * Description of Relative
 *
 * @author bhavik
 */
#[\Attribute]
class Relative
{

    use AttributeTrait {
        verify as parentVerify;
    }

    /**
     * Attribute name.
     */
    const NAME = 'relative';

    /**
     * Relation type loose.
     */
    const LOOSE = 'loose';

    /**
     * Relation type perfect.
     */
    const PERFECT = 'perfect';

    /**
     * Property name on which annotation is defined.
     * 
     * @var string 
     */
    private $propertyName;

    /**
     * 
     * @param string $to
     * @param string|null $type
     * @param string|null $name
     */
    public function __construct(private string $to,
            private ?string $type = null, private ?string $name = null)
    {
        ;
    }

    public function verify()
    {
        $this->parentVerify();

        $this->refactorTo()
                ->refactorType();
    }

    /**
     * Sets property name.
     * 
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
     * 
     * @return self
     * @throws InvalidAttributeException
     */
    private function refactorTo(): self
    {
        if (Nishchay::getEntityCollection()->isExist($this->to) === false) {
            throw new InvalidAttributeException('Relative class [' . $this->to . '] defiend for'
                            . ' property [' . $this->class . '::' . $this->propertyName
                            . '] does not exist.', $this->class, null, 911026);
        }

        return $this;
    }

    /**
     * 
     * @return self
     * @throws InvalidAttributeException
     */
    protected function refactorType(): self
    {
        $this->type = strtolower($this->type);
        $allowed = [
            self::LOOSE => Query::LEFT_JOIN,
            self::PERFECT => Query::INNER_JOIN
        ];

        if (!array_key_exists($this->type, $allowed)) {
            throw new InvalidAttributeException('Invalid relative type [' .
                            $this->type . '] for property [' . $this->class . '::' .
                            $this->propertyName . '].', $this->class, null,
                            911027);
        }

        $this->type = $allowed[$this->type];

        return $this;
    }

}
