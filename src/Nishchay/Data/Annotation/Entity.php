<?php

namespace Nishchay\Data\Annotation;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\InvalidAnnotationParameterException;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Utility\StringUtility;

/**
 * Entity annotation class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Entity extends BaseAnnotationDefinition
{

    /**
     * Static data table name.
     */
    const STATIC_TABLE_NAME = 'StaticData';

    /**
     * Table name for entity.
     * 
     * @var stirng 
     */
    private $name = false;

    /**
     * Convert table name to case.
     * 
     * @var string 
     */
    private $case = 'same';

    /**
     * Separator for entity class name to entity name.
     * By default replaces slash(\) with below.
     * 
     * @var stirng 
     */
    private $separator = '_';

    /**
     * Reserved entity names.
     * 
     * @var array 
     */
    private $reserved = [self::STATIC_TABLE_NAME];

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $priority = ['seperator', 'case', 'name'];
        $this->setter(ArrayUtility::customeKeySort($parameter, $priority), 'parameter');
    }

    /**
     * Returns name of entity.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns case for the table name.
     * 
     * @return string
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Returns separator.
     * 
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Sets table name.
     * 
     * @param   string                      $name
     * @throws  NotSupportedException
     */
    protected function setName($name)
    {
        if ($name === 'this' || $name === 'this.base') {
            if (strpos($name, 'base')) {
                $name = StringUtility::getExplodeLast('\\', $this->class);
            } else {
                $name = str_replace(['\\'], $this->separator, $this->class);
            }
        }

        $callback = [
            'lower' => 'strtolower',
            'upper' => 'strtoupper',
            'camel' => 'lcfirst'
        ];
        $this->name = (array_key_exists($this->case, $callback) ?
                call_user_func($callback[$this->case], $name) :
                $name);

        # Preventing some reserved entity names which should not be used.
        if (in_array(strtolower($this->name), $this->reserved)) {
            throw new NotSupportedException('[' . $this->name . '] is reserved'
                    . ' entity name.', $this->class, null, 911032);
        }
    }

    /**
     * Sets case for entity name.
     * 
     * @param string $case
     */
    protected function setCase($case)
    {
        $case = strtolower($case);
        if (!in_array($case, ['same', 'lower', 'upper'])) {
            throw new InvalidAnnotationParameterException('Invalid annotation'
                    . ' parameter value for [case] of'
                    . ' entity annotation.', $this->class, null, 911033);
        }

        $this->case = $case;
    }

    /**
     * Sets separator.
     * 
     * @param stirng $separator
     */
    protected function setSeparator($separator)
    {
        $this->separator = $separator;
    }

}
