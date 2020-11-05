<?php

namespace Nishchay\Data\Annotation\Property;

use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Utility\MethodInvokerTrait;
use Nishchay\Validation\Rules\{
    MixedRule,
    DateRule,
    StringRule,
    NumberRule
};

/**
 * Validation annotation class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Validation extends BaseAnnotationDefinition
{

    use MethodInvokerTrait;

    private $supportedRules = [
        MixedRule::NAME => MixedRule::class,
        DateRule::NAME => DateRule::class,
        StringRule::NAME => StringRule::class,
        NumberRule::NAME => NumberRule::class,
    ];

    /**
     * Callback method name.
     * 
     * @var string
     */
    private $callback = false;

    /**
     * Type of validation.
     * 
     * @var string
     */
    private $rule = false;

    /**
     * Actual validation rule.
     * 
     * @var string
     */
    private $actualRule;

    /**
     * Parameter for the type validation.
     * 
     * @var array
     */
    private $parameter = [];

    /**
     * 
     * @param type $class
     * @param type $method
     * @param type $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $this->check($parameter);
        $this->setter($parameter, 'parameter');
    }

    /**
     * Checks parameter.
     * 
     * @param type $parameter
     * @throws InvalidAnnotationExecption
     */
    private function check($parameter)
    {
        if (isset($parameter['callback']) === false && isset($parameter['rule']) === false) {
            throw new InvalidAnnotationExecption('Annotation [validation]'
                    . ' requires one of callback or type paramter.', $this->class);
        }

        if (isset($parameter['callback']) && isset($parameter['rule'])) {
            throw new InvalidAnnotationExecption('Annotation paramter can'
                    . ' callback and type can not used together.', $this->class);
        }
    }

    /**
     * Returns callback function.
     * 
     * @return array
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Sets callback parameter.
     * 
     * @param strinng $callback
     */
    protected function setCallback($callback)
    {
        $expl = explode('::', $callback);
        $class = count($expl) > 1 ? $expl[0] : $this->class;
        $method = count($expl) > 1 ? $expl[1] : $expl[0];

        if ($this->isCallbackExist([$class, $method]) === false) {
            throw new InvalidAnnotationExecption('Validation callback method ['
                    . $class . '::' . $method . '] does not exists.',
                    $this->class, null, 911028);
        }
        $this->callback = ['class' => $class, 'method' => $method];
    }

    /**
     * Returns type of validation.
     * 
     * @return strinig|bool
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Returns parameter for the type validation.
     * 
     * @return array
     */
    public function getParameter(): array
    {
        return $this->parameter;
    }

    /**
     * Sets type validation.
     * 
     * @param string $rule
     * @return $this
     */
    protected function setRule(string $rule)
    {
        $this->actualRule = $rule;
        if (strpos($rule, ':') === false) {
            $rule = MixedRule::NAME . ':' . $rule;
        }

        list ($ruleType, $ruleName) = explode(':', $rule);
        if (isset($this->supportedRules[$ruleType]) === false || method_exists($this->supportedRules[$ruleType], $ruleName) === false) {
            throw new InvalidAnnotationExecption('Rule [' . $rule . '] does not exists.', $this->class);
        }

        $this->rule = [$this->supportedRules[$ruleType], $ruleName];
        return $this;
    }

    /**
     * Returns actual rule name.
     * 
     * @return string
     */
    public function getActualRule(): ?string
    {
        return $this->actualRule;
    }

    /**
     * Sets parameter for the type validation.
     * 
     * @param array|string $param
     * @return $this
     */
    protected function setParameter($param)
    {
        $this->parameter = (array) $param;

        foreach ($this->parameter as $key => $value) {
            if (is_numeric($value)) {
                $this->parameter[$key] = (double) $value;
            }
        }
        return $this;
    }

}
