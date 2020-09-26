<?php

namespace Nishchay\Validation;

/**
 * Validation message trait.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
trait MessageTrait
{

    /**
     * Returns prepared message for the validation.
     * 
     * @param string $fieldName
     * @param string $ruleName
     * @param array $params
     * @return string
     */
    public function getPreparedMessage(string $message, string $fieldName, string $ruleName, array $params)
    {
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
