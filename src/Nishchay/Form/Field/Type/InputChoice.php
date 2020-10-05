<?php

namespace Nishchay\Form\Field\Type;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\ApplicationException;
use Nishchay\Form\Field\AbstractField;

/**
 * Input choice class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class InputChoice extends AbstractField
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
     * Returns type of input.
     * 
     * @param string $type
     * @return $this
     */
    public function setType(?string $type)
    {
        if (!in_array($type, ['radio', 'checkbox'])) {
            throw new NotSupportedException('Input choice field type [' . $type .
                    '] not supported.', 1, null, 918002);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Prints input.
     * 
     * @return string
     */
    public function __toString()
    {
        $inputs = [];
        foreach ($this->getChoices() as $value => $html) {
            $inputs[] = $this->getChoice($value);
        }
        return implode('', $inputs);
    }

    /**
     * Returns single choice of given name in HTML form as string.
     * 
     * @param string $name
     * @return string
     * @throws ApplicationException
     */
    public function getChoice(string $name, bool $printHTML = true): string
    {
        $choices = $this->getChoices();

        if (array_key_exists($name, $choices) === false) {
            throw new ApplicationException('Form field [' . $this->getName() .
                    '] does not have choice named [' . $name . '].', 1, null, 918003);
        }

        return $this->printChoice($name, $choices[$name], $printHTML);
    }

    /**
     * Returns HTML value of choice name.
     * 
     * @param string $name
     * @return string
     * @throws ApplicationException
     */
    public function getChoiceHTML(string $name)
    {
        $choices = $this->getChoices();

        if (array_key_exists($name, $choices) === false) {
            throw new ApplicationException('Form field [' . $this->getName() .
                    '] does not have choice named [' . $name . '].', 1, null, 918003);
        }

        return $choices[$name];
    }

    /**
     * Returns input choice in HTML form as string.
     * 
     * @param string $value
     * @param string $html
     * @param boolean $printHTML
     * @return string
     */
    private function printChoice(string $value, $html, bool $printHTML = true): string
    {
        return '<input ' .
                $this->printName() . ' ' .
                $this->printType() . ' ' .
                $this->printAttributes() . ' ' .
                'value="' . $value . '" ' .
                ($this->value === $value ? 'checked' : '') .
                ' /> ' .
                ($printHTML ? $html : '');
    }

}
