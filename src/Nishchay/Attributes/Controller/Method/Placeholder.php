<?php

namespace Nishchay\Attributes\Controller\Method;

use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Attributes\AttributeTrait;

/**
 * Route placeholder attribute class.
 *
 * @license     http://www.Nishchaysource.com/license New BSD License
 * @copyright   (c) 2017, Nishchay Source
 * @author      Bhavik Patel
 */
#[\Attribute]
class Placeholder
{

    use AttributeTrait;

    /**
     * Attribute name.
     */
    const NAME = 'placeholder';

    /**
     * Actual placeholders received from attribute.
     * 
     * @var array
     */
    private array $actualPlaceholders = [];

    /**
     * Supported dynamic placeholder type.
     * 
     * @var array 
     */
    private array $supported = [
        'string' => '([a-zA-Z0-9\._-]+)',
        'number' => '([0-9]+)',
        'int' => '([0-9]+)',
        'alphanum' => '([a-zA-Z0-9]+)',
        'bool' => '(0|1){1}',
        'boolean' => '(0|1){1}',
    ];

    /**
     * 
     * @var Route
     */
    private Route $route;

    /**
     * 
     * @var array
     */
    private array $pattern = [];

    /**
     * Parsed placeholders prepared from actual placeholder of attribute.
     * 
     * @var array
     */
    private array $placeholder = [];

    /**
     * 
     * @param array $placeholders
     */
    public function __construct(array $placeholders)
    {
        $this->actualPlaceholders = $placeholders;
    }

    /**
     * 
     * @param Route $route
     * @return $this
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * 
     * @throws InvalidAttributeException
     * @throws NotSupportedException
     */
    public function verifyParameters()
    {
        if (empty($this->actualPlaceholders)) {
            throw new InvalidAttributeException(message: '[' . __CLASS__ . '] requires parameters to be passed.',
                            code: 914032);
        }


        # Checking if there is placeholder value mismatch.
        $diff = array_diff(array_keys($this->actualPlaceholders),
                $this->route->getPlaceholder());
        if (count($diff) > 0) {
            throw new InvalidAttributeException('Route placeholder values  mismatch. ['
                            . implode(',', $diff) . '] does not exist in route path.',
                            $this->class, $this->method, 914035);
        }

        foreach ($this->actualPlaceholders as $name => $type) {
            if ((is_string($type) && array_key_exists($type, $this->supported) === false) || (!is_string($type) && !is_array($type))) {
                throw new NotSupportedException('Placeholder segment type [' .
                                $type . '] not supported.', $this->class,
                                $this->method, 914036);
            }

            if (is_string($type)) {
                $regex = $this->supported[$type];
            } else {
                $type = array_map('preg_quote', $type);
                $regex = '((' . implode('|', $type) . '){1})';
            }
            $this->pattern['#{' . $name . '}#'] = $regex;

            # We are making pattern named pattern so we can retrieve placeholder
            # name value from it. Below code just inserts named pattern into
            # existing pattern.
            $this->placeholder['#{' . $name . '}#'] = substr_replace($regex,
                    '?P<' . $name . '>', 1, 0);
        }
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

}
