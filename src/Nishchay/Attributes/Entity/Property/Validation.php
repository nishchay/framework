<?php

namespace Nishchay\Attributes\Entity\Property;

use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Attributes\AttributeTrait;
use \Nishchay\Utility\MethodInvokerTrait;
use Nishchay\Validation\Rules\{
    MixedRule,
    DateRule,
    StringRule,
    NumberRule
};

/**
 * Description of Validation
 *
 * @author bhavik
 */
#[\Attribute]
class Validation
{

    use AttributeTrait {
        verify as parentVerify;
    }
    use MethodInvokerTrait;

    /**
     * Attribute name.
     */
    const NAME = 'validation';

    /**
     * Supported rules.
     */
    const SUPPORTED_RULES = [
        MixedRule::NAME => MixedRule::class,
        DateRule::NAME => DateRule::class,
        StringRule::NAME => StringRule::class,
        NumberRule::NAME => NumberRule::class,
    ];

    /**
     * Actual validation rule.
     * 
     * @var string
     */
    private $refactorRule;

    public function __construct(private ?string $rule = null,
            private array $parameter = [], private ?string $callback = null)
    {
        
    }

    /**
     * Verifies rule and callback parameter for its validation.
     * 
     * @throws InvalidAttributeException
     */
    public function verify()
    {
        $this->parentVerify();
        if ($this->rule === null && $this->callback === null) {
            throw new InvalidAttributeException('Attribute [validation]'
                            . ' requires one of [callback] or [rule] paramter.',
                            $this->class, null, 911092);
        }

        if ($this->rule !== null && $this->callback !== null) {
            throw new InvalidAttributeException('Attribute paramter '
                            . ' [callback] and [rule] can not used together.',
                            $this->class, null, 911093);
        }
        $this->verifyCallback()
                ->verifyRule();
    }

    /**
     * Verifies callback parameter.
     */
    protected function verifyCallback()
    {

        if ($this->callback === null) {
            return $this;
        }

        $expl = explode('::', $this->callback);
        $class = count($expl) > 1 ? $expl[0] : $this->class;
        $method = count($expl) > 1 ? $expl[1] : $expl[0];

        if ($this->isCallbackExist([$class, $method]) === false) {
            throw new InvalidAttributeException('Validation callback method ['
                            . $class . '::' . $method . '] does not exists.',
                            $this->class, null, 911028);
        }
        $this->callback = ['class' => $class, 'method' => $method];

        return $this;
    }

    /**
     * Verifies rule parameter.
     * 
     * @return $this
     */
    protected function verifyRule()
    {
        if ($this->rule === null) {
            return $this;
        }

        $this->refactorRule = $this->rule;
        if (strpos($this->rule, ':') === false) {
            $this->refactorRule = MixedRule::NAME . ':' . $this->refactorRule;
        }

        list ($ruleType, $ruleName) = explode(':', $this->refactorRule);
        if (isset(self::SUPPORTED_RULES[$ruleType]) === false || method_exists(self::SUPPORTED_RULES[$ruleType],
                        $ruleName) === false) {
            throw new InvalidAttributeException('Rule [' . $this->rule . '] does not exists.',
                            $this->class, null, 911094);
        }

        $this->refactorRule = [self::SUPPORTED_RULES[$ruleType], $ruleName];
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getRule(): array
    {
        return $this->refactorRule;
    }

    /**
     * Returns actual rule as received from parameter.
     * 
     * @return string
     */
    public function getActualRule()
    {
        return $this->rule;
    }

}
