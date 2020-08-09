<?php

namespace Nishchay\Form\Field;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Http\Request\Request;

/**
 * Abstract Field class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractField
{

    /**
     * Name of form input.
     * 
     * @var string 
     */
    protected $name;

    /**
     * Type of form input.
     * 
     * @var string
     */
    protected $type;

    /**
     * Value of form input.
     * 
     * @var string
     */
    protected $value;

    /**
     * Request method.
     * 
     * @var string
     */
    private $requestMethod;

    /**
     * Choices for form input.
     * 
     * @var array
     */
    protected $choices = [];

    /**
     * Other attributes of input.
     * 
     * @var array
     */
    protected $attributes = [];

    /**
     * Input attribute which are not added to attributes property of this class.
     * 
     * @var array
     */
    protected static $reserved = ['name', 'type', 'value'];

    /**
     * Validations for input.
     * 
     * @var array
     */
    protected $validation = [];

    /**
     * Validation error messages.
     * 
     * @var array
     */
    private $messages = [];

    /**
     * Is input field an array.
     * 
     * @var boolean
     */
    private $isArray = false;

    /**
     * Number of input field.
     * 
     * @var int
     */
    private $arrayCount = 1;

    /**
     * Initialization.
     * 
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, ?string $type, string $requestMethod)
    {
        $this->init($name, $type, $requestMethod);
    }

    /**
     * Initialization.
     * 
     * @param string $name
     * @param string $type
     * @param string $requestMethod
     */
    private function init(string $name, ?string $type, string $requestMethod)
    {
        $this->setName($name)
                ->setType($type)
                ->setRequestMethod($requestMethod);
    }

    /**
     * Returns name of input.
     * 
     * @return string
     */
    public function getName(bool $onlyName = true)
    {
        return $this->name . ($this->isArray() && $onlyName === false ? '[]' : '');
    }

    /**
     * Returns true if field is an array.
     * 
     * @return bool
     */
    public function isArray(): bool
    {
        return $this->isArray;
    }

    /**
     * Returns number field count.
     * 
     * @return int
     */
    public function getArrayCount(): int
    {
        return $this->arrayCount;
    }

    /**
     * Marks field is an array or not.
     * 
     * @param bool $isArray
     */
    public function setIsArray(bool $isArray)
    {
        $this->isArray = $isArray;
        return $this;
    }

    /**
     * Sets input field array count.
     * 
     * @param int $arrayCount
     */
    public function setArrayCount(int $arrayCount)
    {
        $this->arrayCount = $arrayCount;
        return $this;
    }

    /**
     * Returns type of input.
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns value of input.
     * 
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns choices of input.
     * 
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * Returns attributes of input except name, type and value.
     * 
     * @return array
     */
    public function getAttributes(?string $name = null)
    {
        if ($name === null) {
            return $this->attributes;
        }

        return array_key_exists($name, $this->attributes) ?
                $this->attributes[$name] : '';
    }

    /**
     * Sets name of input.
     * 
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns type of input.
     * 
     * @param string $type
     * @return $this
     */
    public function setType(?string $type)
    {
        if ($type === null) {
            return $this;
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Sets request method.
     * 
     * @param string $requestMethod
     */
    private function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

    /**
     * Returns request parameter value.
     * 
     * @return mixed
     */
    public function getRequest()
    {
        $name = $this->getName();
        switch ($this->requestMethod) {
            case Request::POST:
            case Request::DELETE:
            case Request::PUT:
            case Request::PATCH:
                if ($this->getType() === 'file') {
                    return Request::file($name);
                }
                return Request::post($name);
            case Request::GET:
            default :
                return Request::get($name);
        }
    }

    /**
     * Sets value of input.
     * 
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Sets choices of input.
     * 
     * @param array $choices
     * @return $this
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;
        return $this;
    }

    /**
     * Sets attribute
     * $name can be string or array.
     * $name and $value should be string if want to set only one attribute
     * use $name = array otherwise containing list of attributes.
     * 
     * @param array $name
     * @return $this
     */
    public function setAttribute($name, $value = null)
    {
        if (is_string($name)) {
            $name = [$name => $value];
        }

        foreach ($name as $attrName => $attrValue) {
            if (is_string($attrName) === false || is_scalar($attrValue) === false) {
                throw new NotSupportedException('Attribute name and its value'
                        . ' must be string.', 1, null, 918004);
            }

            # Not allowing reserved attribute via this method.
            if (in_array(strtolower($attrName), static::$reserved)) {
                continue;
            }
            $this->attributes[$attrName] = $this->getAttributeValue($attrValue);
        }
        return $this;
    }

    /**
     * Returns printable attribute value.
     * 
     * @param string|boolean $value
     * @return string
     */
    private function getAttributeValue($value)
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return $value;
    }

    /**
     * Make input to be required.
     * To make it optional again pass $required = false.
     * 
     * @param boolean $required
     * @return boolean|$this
     */
    public function isRequired(bool $required = true)
    {
        if ($required === false) {
            if (array_key_exists('required', $this->validation)) {
                unset($this->validation['required']);
            }
            return $this;
        }
        $this->validation['required'] = true;

        return $this;
    }

    /**
     * Sets validation for the field.
     * 
     * @param string|array $rule
     * @param array $params
     * @return $this
     */
    public function setValidation($rule, $params = [])
    {
        if (is_string($rule)) {
            $rule = [$rule => $params];
        }

        foreach ($rule as $ruleName => $ruleParams) {
            $this->validation[$ruleName] = $ruleParams;
        }
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * Returns name with its attribute to be printed.
     * 
     * @return string
     */
    protected function printName($onlyName = true)
    {
        return "name='{$this->getName($onlyName)}'";
    }

    /**
     * Returns type with its attribute to be printed.
     * 
     * @return string
     */
    protected function printType()
    {
        return "type='{$this->getType()}'";
    }

    /**
     * Returns value with its attribute to be printed.
     * 
     * @return string
     */
    protected function printValue()
    {
        if ($this->getValue() === null) {
            return '';
        }
        return 'value="' . addslashes($this->getValue()) . '" ';
    }

    /**
     * Returns attributes a string by attributeName = attributeValue.
     * 
     * @return string
     */
    protected function printAttributes()
    {
        $attributes = [];
        foreach ($this->getAttributes() as $attrName => $attrValue) {
            $attributes[] = $attrName . '="' . str_replace('"', '\"', $attrValue) . '" ';
        }

        return implode(' ', $attributes);
    }

    /**
     * 
     * @param type $ruleName
     * @param type $message
     */
    public function setMessage($ruleName, $message = null)
    {
        if (is_string($message)) {
            $message = [$ruleName => $message];
        }

        foreach ($message as $name => $text) {
            $this->messages[$name] = $text;
        }
        return $this;
    }

    /**
     * 
     * @return type
     */
    public function getMessages()
    {
        return $this->messages;
    }

}
