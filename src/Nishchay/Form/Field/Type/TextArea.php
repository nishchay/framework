<?php

namespace Nishchay\Form\Field\Type;

use Nishchay\Form\Field\AbstractField;

/**
 * Description of TextArea
 *
 * @author bpatel
 */
class TextArea extends AbstractField
{

    /**
     * Initialization
     * 
     * @param string $name
     * @param string $requestMethod
     */
    public function __construct(string $name, string $requestMethod)
    {
        parent::__construct($name, null, $requestMethod);
    }

    /**
     * Returns text area field as string
     * 
     * @return string
     */
    public function getSingle(): string
    {
        return '<textarea' .
                ' ' . $this->printName(false) .
                $this->printAttributes() . ' >' .
                $this->getValue() .
                '</textarea>';
    }

    /**
     * Returns textarea field as string.
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
