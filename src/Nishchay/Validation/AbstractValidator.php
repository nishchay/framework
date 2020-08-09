<?php

namespace Nishchay\Validation;

use Nishchay\Exception\ApplicationException;
use Nishchay\Validation\Rules\AbstractRule;

/**
 * Description of AbstractValidator
 *
 * @author bpatel
 */
abstract class AbstractValidator
{

    /**
     * Custom error messages.
     * 
     * @var array
     */
    protected $customMessges = [];

    /**
     * Returns message from custom message list if it exists for field otherwise
     * it returns FALSE.
     * 
     * @param type $fieldName
     * @param type $ruleName
     * @return boolean
     */
    private function getMessage($fieldName, $ruleName)
    {
        if (!empty($this->customMessges[$fieldName][$ruleName])) {
            return $this->customMessges[$fieldName][$ruleName];
        }
        return false;
    }

    /**
     * Sets custom error messages.
     * 
     * @param string|array $fieldName
     * @param string $ruleName
     * @param string $message
     * @return $this
     */
    public function setMessage($fieldName, $ruleName = null, $message = null)
    {
        if (is_array($fieldName)) {
            foreach ($fieldName as $name => $messages) {
                if (!is_array($messages)) {
                    throw new ApplicationException('Validation error messeges'
                            . ' should be array for field name [' . $name . '].', 1, null, 931003);
                }
                foreach ($messages as $ruleName => $message) {
                    $this->setActualMessage($name, $ruleName, $message);
                }
            }
            return $this;
        }

        $this->setActualMessage($fieldName, $ruleName, $message);
        return $this;
    }

    /**
     * Sets error message to custom message list.
     * 
     * @param type $fieldName
     * @param type $ruleName
     * @param type $message
     */
    private function setActualMessage($fieldName, $ruleName, $message)
    {
        $this->customMessges[$fieldName][$this->getProperRuleName($ruleName)] = $message;
    }

    /**
     * Prefix mixed type before rule name if it does not exist.
     * 
     * @param type $ruleName
     * @return type
     */
    private function getProperRuleName($ruleName)
    {
        if (strpos($ruleName, ':') === false) {
            return 'mixed:' . $ruleName;
        }

        return $ruleName;
    }

    /**
     * Returns validation error message either from defined or custom.
     * 
     * @param AbstractRule $rule
     * @param string $ruleName
     * @param string $fieldName
     * @param array $params
     * @return type
     */
    protected function parseMessage(AbstractRule $rule, $ruleName, $fieldName, $params = [])
    {
        if (($message = $this->getMessage($fieldName, $rule->getName() . ':' . $ruleName)) === false) {
            $message = $rule->getMessage($ruleName);
        }
        return preg_replace_callback('#\{([a-zA-Z-_.0-9]+)\}#', function ($match) use ($fieldName, $ruleName, $params) {
            if ($match[1] === 'field') {
                return $fieldName;
            } else if ($match[1] === 'rule') {
                return $ruleName;
            }

            if (array_key_exists($match[1], $params)) {
                $replacement = $params[$match[1]];
                if (is_array($replacement)) {
                    return '[' . implode(', ', $replacement) . ']';
                }

                return $replacement;
            }

            return $match[0];
        }, $message);
    }

}
