<?php

namespace Nishchay\Validation;

use Exception;
use TypeError;
use Nishchay\Exception\ApplicationException;
use Nishchay\Security\CSRF;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Utility\StringUtility;
use Nishchay\Validation\Rules\MixedRule;
use Nishchay\Http\Request\Request;
use Nishchay\Utility\Coding;
use Nishchay\Validation\Rules\AbstractRule;
use Nishchay\Validation\Rules\DateRule;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * Validator class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Validator extends AbstractValidator
{

    use MethodInvokerTrait;

    /**
     * Mixed rule class name.
     */
    const MIXED_RULE_CLASS = 'MixedRule';

    /**
     * Request method.
     * 
     * @var string 
     */
    private $method;

    /**
     *
     * @var string
     */
    private $requestMethod;

    /**
     * CSRF instance.
     * 
     * @var \Nishchay\Security\CSRF 
     */
    private $csrf;

    /**
     *
     * @var array
     */
    private $validation = [];

    /**
     *
     * @var array
     */
    private $rules = [];

    /**
     * Validation errors.
     * 
     * @var array
     */
    private $errors = [];

    /**
     * 
     * @var array
     */
    private array $data = [];

    /**
     * Initialization.
     * 
     * @param string $method
     */
    public function __construct($method = Request::POST)
    {
        $this->init($method);
    }

    private function init($method)
    {
        $this->method = strtoupper($method);
        $this->requestMethod = strtoupper(Request::server('METHOD'));

        if (MixedRule::isExist('confirm') === false) {
            MixedRule::addRule('confirm',
                    function ($value, $fieldName) {
                return $value === $this->getRequest($fieldName);
            }, '{field} should be same as {0}');
        }

        if (MixedRule::isExist('depends') === false) {
            MixedRule::addRule('depends',
                    function ($value, $fieldName, $shouldBe = null) {
                $fieldValue = $this->getRequest($fieldName);
                if ($shouldBe === null) {
                    return empty($fieldValue) ? true : !empty($value);
                }

                return $fieldValue == $shouldBe ? !empty($value) : true;
            }, '{field} is required for {0}');
        }
    }

    /**
     * Returns custom data.
     * 
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Sets data to be used for validation.
     * This is in case need to do validation on custom data rather than request.
     * 
     * @param array $data
     * @return self
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Returns request value for the input field.
     * 
     * @param string $name
     * @return array
     */
    private function getRequest($name)
    {
        if (!empty($this->data)) {
            return $this->data[$name] ?? false;
        }

        switch ($this->requestMethod) {
            case Request::POST:
            case Request::DELETE:
            case Request::PUT:
            case Request::PATCH:
                if (($file = Request::file($name)) !== false) {
                    return $file;
                }
                return Request::post($name);
            case Request::GET:
            default :
                return Request::get($name);
        }

        return false;
    }

    /**
     * Sets CSRF to be used for this validation.
     * 
     * @param CSRF $csrf
     * @return \Nishchay\Security\CSRF
     */
    public function setCSRF(CSRF $csrf)
    {
        $this->csrf = $csrf;
        return $this;
    }

    /**
     * Returns TRUE if form passes validation.
     * It returns NULL if validation is not performed.
     * Can throw exception if CSRF check fails.
     * 
     * @return boolean
     */
    public function validate()
    {
        if (!$this->canValidate()) {
            return null;
        }
        $this->resetErrors()
                ->verifyCSRF();

        $validated = true;
        foreach ($this->validation as $fieldName => $rule) {
            if ($rule !== 'required' && array_key_exists('required', $rule) === false && empty($this->getRequest($fieldName))) {
                if (array_key_exists('depends', $rule) !== false) {
                    $validated = $this->validateField($fieldName, $rule);
                    if ($validated === false) {
                        return false;
                    }
                } else {
                    continue;
                }
            }

            if ((!$this->validateField($fieldName, $rule))) {
                $validated = false;
            }
        }

        return $validated;
    }

    /**
     * Resets errors to empty.
     * 
     * @return $this
     */
    private function resetErrors()
    {
        $this->errors = [];
        return $this;
    }

    /**
     * Returns true if validation method matches with current request method.
     * 
     * @return boolean
     */
    private function canValidate()
    {
        return $this->requestMethod === $this->method;
    }

    /**
     * Verifies CSRF.
     * 
     * @return null
     */
    private function verifyCSRF()
    {
        if ($this->csrf !== null) {
            $this->csrf->verify();
        }
        return $this;
    }

    /**
     * Returns all errors or error of given field if its not null.
     * 
     * @param type $field
     * @return type
     */
    public function getErrors($field = null)
    {
        if ($field === null) {
            return $this->errors;
        }

        return array_key_exists($field, $this->errors) ? $this->errors[$field] : false;
    }

    /**
     * 
     * @param type $name
     * @param type $rule
     * @return $this
     */
    public function setValidation($name, $rule = null)
    {
        if (is_string($name)) {
            if (strpos($name, ',') === false) {
                $this->setFieldRule($name, $rule);
                return $this;
            }
            foreach (StringUtility::explode(',', $name) as $fieldName) {
                $this->setFieldRule($fieldName, $rule);
            }
            return $this;
        }

        foreach ($name as $fieldName => $fieldRule) {
            $this->setFieldRule($fieldName, $fieldRule);
        }
        return $this;
    }

    /**
     * 
     * @param string $name
     * @param string|array $rule
     * @return $this
     */
    private function setFieldRule($name, $rule)
    {
        if (array_key_exists($name, $this->validation) === false) {
            $this->validation[$name] = [];
        }

        if (is_string($rule)) {
            $rule = [$rule];
        }
        foreach ($rule as $ruleName => $ruleParams) {
            if (is_int($ruleName)) {
                $ruleName = $ruleParams;
                $ruleParams = [];
            }
            if (!is_array($ruleParams)) {
                $ruleParams = [$ruleParams];
            }
            $this->validation[$name][$ruleName] = $ruleParams;
        }
        return $this;
    }

    /**
     * 
     * @param type $type
     * @return \Nishchay\Validation\Rules\AbstractRule
     */
    private function getRule($type)
    {
        if (array_key_exists($type, $this->rules)) {
            return $this->rules[$type];
        }

        $class = Coding::getNamespace(MixedRule::class, self::MIXED_RULE_CLASS)
                . ucfirst($type) . 'Rule';

        if (!class_exists($class)) {
            throw new NotSupportedException('Validation rule type [' .
                            $type . '] not supported.', null, null, 931004);
        }

        return $this->rules[$type] = new $class;
    }

    /**
     * 
     * @param string $fieldName
     * @param type $rules
     * @return boolean
     */
    private function validateField($fieldName, $rules)
    {
        $value = $this->getRequest($fieldName);
        if (is_array($value) === false) {
            $value = [$value];
        }

        foreach ($rules as $ruleName => $ruleParams) {
            $rule = $this->parseRule($ruleName);
            $ruleInstance = $this->getRule($rule['type']);
            if ($ruleInstance instanceof DateRule && $ruleName !== DateRule::FORMAT_NAME) {
                if (empty($rules[DateRule::FORMAT_NAME])) {
                    throw new ApplicationException('Date format is required for each date'
                                    . ' validation.', null, null, 931005);
                }
                array_push($ruleParams, current($rules[DateRule::FORMAT_NAME]));
            }

            foreach ($value as $val) {
                if (!$this->validateRule($ruleInstance, $rule['name'], $val,
                                $ruleParams)) {
                    $this->errors[$fieldName] = $this->parseMessage($ruleInstance,
                            $rule['name'], $fieldName, $ruleParams);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 
     * @param type $ruleName
     * @return type
     */
    private function parseRule($ruleName)
    {
        $type = MixedRule::NAME;
        if (strpos($ruleName, ':') !== false) {
            $explode = explode(':', $ruleName);
            $type = $explode[0];
            $ruleName = $explode[1];
        }

        if ($this->isRuleExist($this->getRule($type), $ruleName) === false &&
                $this->isRuleExist($this->getRule($ruleName), $ruleName) === true) {
            $type = $ruleName;
        }

        return [
            'name' => $ruleName,
            'type' => $type
        ];
    }

    /**
     * Validates single rule.
     * 
     * @param AbstractRule $rule
     * @param string $ruleName
     * @param string $value
     * @param string|array $params
     * @return bool
     * @throws Exception
     */
    private function validateRule(AbstractRule $rule, $ruleName, $value,
            $params = [])
    {
        array_unshift($params, $value);

        # Requiring message to be exists for rule, otherwise we will consider
        # that such rule does not exists.
        if (!$this->isRuleExist($rule, $ruleName) || !$rule->isMessageExists($ruleName)) {
            throw new ApplicationException('Validation [' . $rule->getName() . '::' . $ruleName
                            . '] does not exist.', null, null, 931006);
        }

        # Validation rule method does not throws excepion unless invalid value
        # passed.
        # Just an example for the RequestFile, string:min is used then min
        # results in an TypeError as it accepts only string as parameter.
        try {
            return $this->invokeMethod([$rule, $ruleName], $params);
        } catch (TypeError | Exception $e) {
            return false;
        }
    }

    /**
     * Returns true if rule exists.
     * 
     * @param MixedRule $rule
     * @param type $ruleName
     * @return boolean
     */
    private function isRuleExist($rule, $ruleName)
    {
        if (method_exists($rule, $ruleName) === false) {
            if ($rule instanceof MixedRule && MixedRule::isExist($ruleName)) {
                return true;
            }
            return false;
        }

        return true;
    }

    /**
     * Returns validation rules in an array.
     * 
     * @return array
     */
    public function getValidationRule()
    {
        $validations = [];
        foreach ($this->validation as $fieldName => $rules) {
            $rule = [
                'field' => $fieldName,
                'rule' => $rules
            ];
            if (isset($this->customMessges[$fieldName])) {
                $rule['message'] = $this->customMessges[$fieldName];
            }
            $validations[] = $rule;
        }
        return $validations;
    }

}
