<?php

namespace Nishchay\Form\Field\Type;

use Nishchay\Form\Field\AbstractField;

/**
 * Form input select class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Select extends AbstractField
{

    /**
     * Initialization.
     * 
     * @param string $name
     * @param string $type
     * @param string $requestMethod
     */
    public function __construct(string $name, string $requestMethod)
    {
        parent::__construct($name, 'select', $requestMethod);
    }

    /**
     * Returns input field as string.
     * 
     * @return type
     */
    public function getSingle()
    {
        return '<select ' . $this->printName() .
                ' ' . $this->printValue() .
                $this->printAttributes() . ' >' .
                $this->printChoice() .
                '</select>';
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

    /**
     * 
     * @return string
     */
    private function printChoice()
    {
        $choices = [];

        foreach ($this->getChoices() as $value => $html) {
            $choices[] = '<option value="' . $value . '">' . $html . '</option>';
        }

        return implode('', $choices);
    }

}
