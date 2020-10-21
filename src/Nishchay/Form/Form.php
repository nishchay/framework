<?php

namespace Nishchay\Form;

use Nishchay\Exception\NotSupportedException;
use ReflectionClass;
use ReflectionMethod;
use Nishchay\Security\CSRF;
use Nishchay\Form\Field\AbstractField;
use Nishchay\Validation\Validator;
use Nishchay\Http\Request\Request;
use Nishchay\Form\Field\Type\Button;
use Nishchay\Form\Field\Type\Input;
use Nishchay\Form\Field\Type\InputChoice;
use Nishchay\Form\Field\Type\Select;
use Nishchay\Form\Field\Type\TextArea;
use Nishchay\Processor\FetchSingletonTrait;

/**
 * Nishchay Form class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Form
{

    use FetchSingletonTrait;

    /**
     * Form name.
     * 
     * @var string 
     */
    private $formName;

    /**
     * Form request method.
     * 
     * @var string 
     */
    private $method;

    /**
     * Attributes value.
     * 
     * @var array 
     */
    private $attributes = [
        'action' => ''
    ];

    /**
     * CSRF instance.
     * 
     * @var \Nishchay\Security\CSRF
     */
    private $csrf = null;

    /**
     *
     * @var \Nishchay\Validation\Validator
     */
    private $validator;

    /**
     * Reserverd methods.
     * 
     * @var array 
     */
    private static $reserved = ['method'];

    /**
     * Initialization.
     * 
     * @param string $method
     */
    public function __construct(string $formName, string $method = Request::POST)
    {
        $this->setFormName($formName)
                ->setMethod($method);
    }

    /**
     * Returns form name.
     * 
     * @return string
     */
    final public function getFormName(): string
    {
        return $this->formName;
    }

    /**
     * Sets form name.
     * 
     * @param string $formName
     * @return $this
     */
    final public function setFormName(string $formName): self
    {
        $this->formName = $formName;
        return $this;
    }

    /**
     * Returns CSRF instance.
     * 
     * @return \Nishchay\Security\CSRF
     */
    final public function getCSRF()
    {
        if ($this->csrf !== null) {
            return $this->csrf;
        }
        $this->csrf = new CSRF($this->getFormName());
        return $this->csrf->setWhere($this->getMethod() === Request::GET ? Request::GET : Request::POST);
    }

    /**
     * Disables CSRF check if flag is passed as false.
     * If CSRF has been disabled then pass true to enable it again.
     * 
     * @param boolean $flag
     */
    final protected function removeCSRF(bool $flag = false)
    {
        $this->csrf = $flag === false ? false : null;
    }

    /**
     * Returns form method.
     * 
     * @return string
     */
    final public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Sets form method.
     * 
     * @param string $method
     * @return $this
     */
    final public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Returns attributes of input except name, type and value.
     * 
     * @return array
     */
    final public function getAttributes(?string $name = null)
    {
        if ($name === null) {
            return $this->attributes;
        }
        return array_key_exists($name, $this->attributes) ?
                $this->attributes[$name] : false;
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
            $attributes[] = $attrName . '="' . addslashes($attrValue) . '"';
        }

        return implode(' ', $attributes);
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
    final public function setAttribute($name, $value = null)
    {
        if (is_string($name)) {
            $name = [$name => $value];
        }

        # Iterating over each attribute to validate it then we will add.
        foreach ($name as $attrName => $attrValue) {
            if (is_string($attrName) === false || is_scalar($attrValue) === false) {
                throw new NotSupportedException('Attribute name and its value'
                        . ' must be string.', 1, null, 918005);
            }

            # Not allowing reserved attribute via this method.
            if (in_array(strtolower($attrName), self::$reserved)) {
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
     * Returns start tag of form along with CSRF input field as a string.
     * 
     * @return string
     */
    final public function startForm(bool $csrf = true): string
    {
        return "<form method='{$this->getMethod()}' " . $this->printAttributes() . " >" .
                ($csrf ? $this->printCSRF() : '');
    }

    /**
     * Returns CSRF input field as string.
     * 
     * @return string
     */
    final public function printCSRF(): string
    {
        if ($this->getCSRF() === false || $this->getCSRF()->getWhere() === Request::HEADER) {
            return '';
        }

        return (string) $this->getCSRF();
    }

    /**
     * Returns end tag of form.
     * 
     * @return string
     */
    final public function endForm(): string
    {
        return '</form>';
    }

    /**
     * Returns TRUE if form passes validation.
     * It returns NULL if no validation performed.
     * 
     * @return boolean
     */
    public function validate()
    {
        $validator = $this->getValidator();

        $this->getCSRF() && $validator->setCSRF($this->getCSRF());
        return $validator->validate();
    }

    /**
     * Returns form field if it is valid method.
     * Method should belongs extending class, it should not be static
     * , it must start with get and must return instance of Field.
     * 
     * @param ReflectionMethod $method
     * @return boolean|AbstractField
     */
    private function getFormField(ReflectionMethod $method)
    {
        if ($method->class === __CLASS__ ||
                $method->isStatic() ||
                strpos($method->name, 'get') === false) {
            return false;
        }

        $field = $this->{$method->name}();

        # Method which returns instance of Form field is valid.
        if ($field instanceof AbstractField) {
            return $field;
        }

        return false;
    }

    private function getReflection()
    {
        return $this->getInstance(ReflectionClass::class, [static::class]);
    }

    /**
     * Returns method names which are form field method.
     * 
     * @return array
     */
    public function getFormMethods(): array
    {
        $methods = [];
        # We are here iterating over each method to find form field method.
        foreach ($this->getReflection()->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {

            # We will not proceed if method is not valid form field method or
            # Form field does not have any validation for it.
            if (($field = $this->getFormField($method)) === false ||
                    empty($field->getValidation())) {
                continue;
            }

            $methods[] = $method->name;
        }

        return $methods;
    }

    /**
     * Returns Validator instance.
     * 
     * @return \Nishchay\Validation\Validator
     */
    private function getValidator()
    {
        if ($this->validator !== null) {
            return $this->validator;
        }

        $this->validator = new Validator($this->getMethod());

        $validation = [];

        # We are here iterating over each method to find form field method.
        foreach ($this->getReflection()->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {

            # We will not proceed if method is not valid form field method or
            # Form field does not have any validation for it.
            if (($field = $this->getFormField($method)) === false ||
                    empty($field->getValidation())) {
                continue;
            }

            $validation[$field->getName()] = $field->getValidation();

            # Iterating over each messeges to add it to validator cusotm message
            # list.
            foreach ($field->getMessages() as $ruleName => $message) {
                $this->validator->setMessage($field->getName(), $ruleName, $message);
            }
        }
        $this->validator->setValidation($validation);
        return $this->validator;
    }

    /**
     * Returns error all messages or message of field if its not passed as null.
     * 
     * @param string $field
     * @return array|string
     */
    public function getErrors($field = null)
    {
        return $this->getValidator()->getErrors($field);
    }

    /**
     * 
     * @return array
     */
    public function getValidationRule()
    {
        return $this->getValidator()->getValidationRule();
    }

    /**
     * Returns new button field.
     * 
     * @param string $name
     * @param string $type
     * @return Button
     */
    protected function newButton(string $name, string $type): Button
    {
        return (new Button($name, $type, $this->getMethod()));
    }

    /**
     * Returns new input field.
     * 
     * @param string $name
     * @param string $type
     * @return Input
     */
    protected function newInput(string $name, string $type): Input
    {
        return (new Input($name, $type, $this->getMethod()));
    }

    /**
     * Returns new input choice field.
     * 
     * @param string $name
     * @param string $type
     * @return InputChoice
     */
    protected function newInputChoice(string $name, string $type): InputChoice
    {
        return (new InputChoice($name, $type, $this->getMethod()));
    }

    /**
     * Returns new select field.
     * 
     * @param string $name
     * @param string $type
     * @return Select
     */
    protected function newSelect(string $name): Select
    {
        return (new Select($name, $this->getMethod()));
    }

    /**
     * Returns new textarea field.
     * 
     * @param string $name
     * @param string $type
     * @return TextArea
     */
    protected function newTextArea(string $name): TextArea
    {
        return (new TextArea($name, $this->getMethod()));
    }

}
