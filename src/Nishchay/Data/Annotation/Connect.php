<?php

namespace Nishchay\Data\Annotation;

use Nishchay\Exception\ApplicationException;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Data\Connection\Connection;

/**
 * Connect annotation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Connect extends BaseAnnotationDefinition
{

    /**
     * Name of connection.
     * 
     * @var stirng 
     */
    private $name = false;

    /**
     * 
     * @param stirng $class
     * @param string $method
     * @param array $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $this->setter($parameter, 'parameter');
    }

    /**
     * Returns database connection name.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets database connection name.
     * 
     * @param string $name
     */
    protected function setName($name)
    {
        if (!Connection::isConnnectionExist($name)) {
            throw new ApplicationException('Database connection [' . $name . ']'
                    . ' does not exist.', $this->class, $this->method, 911031);
        }
        $this->name = $name;
    }

}
