<?php

namespace Nishchay\Route\Annotation;

use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Annotation\BaseAnnotationDefinition;

/**
 * Route placeholder annotation class.
 *
 * @license     http://www.Nishchaysource.com/license New BSD License
 * @copyright   (c) 2017, Nishchay Source
 * @version     1.0
 * @author      Bhavik Patel
 */
class Placeholder extends BaseAnnotationDefinition
{

    /**
     * Placeholders.
     * 
     * @var array 
     */
    private $placeholder = [];

    /**
     * Actual placeholder defined on annotation.
     * 
     * @var array
     */
    private $actualPlaceholder = [];

    /**
     * Placeholder patterns.
     * 
     * @var array 
     */
    private $pattern = [];

    /**
     * Supported dynamic placeholder type.
     * 
     * @var array 
     */
    private $supported = [
        'string' => '([a-zA-Z0-9_-]+)',
        'number' => '([0-9]+)',
        'int' => '([0-9]+)',
        'alphanum' => '([a-zA-Z0-9]+)',
        'bool' => '(0|1){1}',
        'boolean' => '(0|1){1}',
    ];

    /**
     * Route annotation associated with this annotation.
     * 
     * @var object 
     */
    private $route = false;

    /**
     * 
     * @param   string    $class
     * @param   string    $method
     * @param   array     $placeholder
     * @param   object    $route
     */
    public function __construct($class, $method, $placeholder, $route)
    {
        parent::__construct($class, $method);
        $this->route = $route;
        $this->actualPlaceholder = $placeholder;
        $this->setPlaceholder($placeholder);
    }

    /**
     * 
     * @return array
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * Returns placeholder type.
     * 
     * @param string $name
     * @return type
     */
    public function getPlaceholderType(string $name)
    {
        return $this->actualPlaceholder[$name] ?? false;
    }

    /**
     * Sets placeholders.
     * 
     * @param array $placeholders
     * @throws ApplicationException
     * @throws NotSupportedException
     */
    protected function setPlaceholder($placeholders)
    {
        if (is_array($placeholders) === false || empty($placeholders)) {
            throw new InvalidAnnotationExecption('Invalid annotation [placeholder].', $this->class, $this->method, 926012);
        }

        # Checking if there is placeholder value mismatch.
        $diff = array_diff(array_keys($placeholders),
                $this->route->getPlaceholderValues());
        if (count($diff) > 0) {
            throw new ApplicationException('Route placeholder values  mismatch. ['
                    . implode(',', $diff) . '] does not exist in route path.',
                    $this->class, $this->method, 926003);
        }

        foreach ($placeholders as $key => $value) {
            if (is_string($value) && array_key_exists($value, $this->supported) === false) {
                throw new NotSupportedException('Placeholder segment type [' .
                        $value . '] not supported.', $this->class, $this->method, 926004);
            }

            if (is_string($value)) {
                $regex = $this->supported[$value];
            } else {
                $value = array_map('preg_quote', $value);
                $regex = '((' . implode('|', $value) . '){1})';
            }
            $this->pattern['#{' . $key . '}#'] = $regex;

            # We are making pattern named pattern so we can retrieve placeholder
            # name value from it. Below code just inserts named pattern into
            # existing pattern.
            $this->placeholder['#{' . $key . '}#'] = substr_replace($regex,
                    '?P<' . $key . '>', 1, 0);
        }
    }

    /**
     * 
     * @return array
     */
    public function getPattern()
    {
        return $this->pattern;
    }

}
