<?php

namespace Nishchay\Attributes\Controller\Method;

use Nishchay\Attributes\AttributeTrait;

/**
 * Route placeholder attribute class.
 *
 * @license     http://www.Nishchaysource.com/license New BSD License
 * @copyright   (c) 2017, Nishchay Source
 * @author      Bhavik Patel
 */
#[\Attribute]
class NamedScope
{

    const NAME = 'namedscope';

    use AttributeTrait;

    public function __construct(private string|array $name,
            private ?string $default = null)
    {
        $this->verifyName();
    }

    /**
     * Sets name of scope.
     * 
     * @param string $name
     */
    private function verifyName()
    {
        $this->name = (array) $this->name;

        foreach ($this->name as $value) {
            if (is_string($value) === false || empty($value)) {
                throw new ApplicationException('Invalid named scope, all scope name should be string.',
                                $this->class, $this->method, 926013);
            }
            Nishchay::getScopeCollection()->store($value);
        }

        return $this;
    }

    /**
     * Returns default scope.
     * 
     * @return string
     */
    public function getDefault()
    {
        if ($this->default !== false) {
            return $this->default;
        }

        return current($this->name);
    }

    /**
     * Sets default scope.
     * 
     * @param string $default
     */
    public function setDefault(string $default)
    {
        $this->default = $default;
    }

}
