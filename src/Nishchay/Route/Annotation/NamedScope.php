<?php

namespace Nishchay\Route\Annotation;

use Nishchay\Annotation\BaseAnnotationDefinition;

/**
 * Session scope annotation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class NamedScope extends BaseAnnotationDefinition
{

    /**
     * Name of scope.
     * 
     * @var string|array 
     */
    private $name = false;

    /**
     * Default scope name.
     * 
     * @var array 
     */
    private $default = false;

    /**
     * 
     * @param   string  $class
     * @param   string  $method
     * @param   array   $parameter
     */
    public function __construct(string $class, string $method, array $parameter)
    {
        parent::__construct($class, $method);
        $this->setter($parameter);
    }

    /**
     * Returns name of scope.
     * 
     * @return string|array
     */
    public function getName(): array
    {
        return $this->name;
    }

    /**
     * Sets name of scope.
     * 
     * @param string $name
     */
    protected function setName($name)
    {
        if (!is_array($name)) {
            $name = [$name];
        }
        $this->name = $name;
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
