<?php

namespace Nishchay\Form\Field\Type;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Form\Field\AbstractField;

/**
 * Input form field
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Input extends AbstractField
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
     * Sets type of input.
     * 
     * @param string $type
     * @return $this
     */
    public function setType(?string $type)
    {
        if (in_array($type, ['radio', 'checkbox'])) {
            throw new NotSupportedException('Use [' . InputChoice::class . '] for'
                    . $type . ' input.', null, null, 918001);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Returns input field as string.
     * 
     * @return string
     */
    public function getSingle()
    {
        return "<input " .
                $this->printName(false) . ' ' .
                $this->printType() . ' ' .
                $this->printValue() . ' ' .
                $this->printAttributes() .
                ' />';
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
