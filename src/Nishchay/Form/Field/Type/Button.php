<?php

namespace Nishchay\Form\Field\Type;

use Nishchay\Form\Field\AbstractField;

/**
 * Form input button class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Button extends AbstractField
{

    /**
     * Initialization.
     * 
     * @param string $name
     * @param string $type
     * @param string $requestMethod
     */
    public function __construct(string $name, string $type, string $requestMethod)
    {
        parent::__construct($name, $type, $requestMethod);
    }

    /**
     * Returns input field as string.
     * 
     * @return string
     */
    public function getSingle()
    {
        return '<button ' .
                $this->printName(false) . ' ' .
                $this->printType() . ' ' .
                $this->printAttributes() . ' >' .
                $this->getValue() .
                '</button>';
    }

    /**
     * Returns input field as string.
     * 
     * @return string
     */
    public function __toString()
    {
        $fields = [];
        for ($i = 0; $i < $this->getArrayCount(); $i++) {
            $fields[] = $this->getSingle();
        }
        return implode(PHP_EOL, $fields);
    }

}
