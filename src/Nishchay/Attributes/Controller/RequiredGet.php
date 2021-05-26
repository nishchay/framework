<?php

namespace Nishchay\Attributes\Controller;

use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Attributes\AttributeTrait;
use Attribute;

/**
 * Required Get attribute class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class RequiredGet
{

    const NAME = 'requiredGet';

    use AttributeTrait {
        verify as parentVerify;
    }

    /**
     * 
     * @param array $parameter
     */
    public function __construct(private array $parameter,
            private ?string $redirect = null)
    {
        
    }

    /**
     * 
     * @throws InvalidAttributeException
     */
    public function verify()
    {
        $this->parentVerify();
        foreach ($this->parameter as $name) {
            if (!is_string($name)) {
                throw new InvalidAttributeException('List of all parameter '
                                . 'passed in [' . __CLASS__ . '] attribute must '
                                . 'be string.', $this->class, $this->method);
            }
        }
    }

}
