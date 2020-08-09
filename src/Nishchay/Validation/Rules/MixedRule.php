<?php

namespace Nishchay\Validation\Rules;

use Exception;
use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * Class for other type of validation.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class MixedRule extends AbstractRule
{

    use MethodInvokerTrait;

    /**
     * Date rule name.
     */
    const NAME = 'mixed';

    /**
     * Error messages.
     * 
     * @var array 
     */
    protected static $messeges = [
        'required' => '{field} is required.',
        'url' => '{field} should be valid url.',
        'email' => '{field} should be valid email.',
        'ip' => '{field} should be valid IP.',
        'ipv4' => '{field} should be valid IPv4.',
        'ipv6' => '{field} should be valid IPv6.',
        'mac' => '{field} should be valid MAC address.',
        'enum' => '{field} should should be one of {0}',
        'minCount' => 'Count of {field} should be at least {0}',
        'maxCount' => 'Count of {field} should not be greater than {0}'
    ];

    /**
     * List of custom rule.
     * 
     * @var array 
     */
    private static $rules = [];

    /**
     * Returns true only.
     * 
     * @return bool
     */
    public function optional()
    {
        return true;
    }

    /**
     * Returns if $value is not empty.
     * 
     * @param   string      $value
     * @return  bool
     */
    public function required($value): bool
    {
        return empty($value) === false;
    }

    /**
     * Returns true if $value is valid url.
     * 
     * @param   string      $value
     * @return  bool
     */
    public function url(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Returns true if $value is email.
     * 
     * @param   string      $value
     * @return  bool
     */
    public function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Returns true if $value is IP address.
     * 
     * @param string $value
     * @return bool
     */
    public function ip(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Returns true if $value is IPv4.
     * 
     * @param string $value
     * @return bool
     */
    public function ipv4(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Returns true if $value is IPv6.
     * 
     * @param string $value
     * @return bool
     */
    public function ipv6(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Returns true if $value is MAC address.
     * 
     * @param string $value
     * @return bool
     */
    public function mac(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_MAC) !== false;
    }

    /**
     * Returns TRUE if $value belongs to $list.
     * If $caseSensitive is FALSE then cases are ignored.
     * 
     * @param string $value
     * @param array $list
     * @param bool $caseSensitive
     * @return bool
     */
    public function enum($value, array $list, bool $caseSensitive = true): bool
    {
        if ($caseSensitive === false) {
            $value = strtolower($value);
            $list = array_map('strtolower', $list);
        }

        return in_array($value, $list);
    }

    /**
     * Returns true if array count is greater than $count.
     * 
     * @param string|array $value
     * @param int $count
     * @return bool
     */
    public function minCount($value, int $count): bool
    {
        if (is_string($value)) {
            $value = [$value];
        }

        return count($value) >= $count;
    }

    /**
     * Returns true if array count is less than $count.
     * 
     * @param string|array $value
     * @param int $count
     * @return bool
     */
    public function maxCount($value, int $count): bool
    {
        if (is_string($value)) {
            $value = [$value];
        }

        return count($value) <= $count;
    }

    /**
     * Adds new rule.
     * 
     * @param string $name
     * @param mixed $closure
     * @param string $message
     */
    public static function addRule(string $name, \Closure $closure, string $message): void
    {
        if (static::isExist($name)) {
            throw new ApplicationException('Rule [' . $name . '] already exist.', 1, null, 931001);
        }

        static::$rules[$name] = $closure;
        static::$messeges[$name] = $message;
    }

    /**
     * Returns TRUE rule with $name already exist.
     * 
     * @param type $name
     * @return type
     */
    public static function isExist(string $name)
    {
        if (method_exists(static::class, $name)) {
            return true;
        }
        return array_key_exists($name, static::$rules);
    }

    /**
     * 
     * @param type $name
     * @param type $arguments
     * @return type
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if (static::isExist($name, static::$rules)) {
            return $this->invokeMethod(static::$rules[$name], $arguments);
        }

        throw new ApplicationException('Invalid rule [' . $name . '].', null, null, 931002);
    }

    /**
     * Returns name of validation type.
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

}
