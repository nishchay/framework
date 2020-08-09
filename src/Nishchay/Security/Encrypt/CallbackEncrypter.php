<?php

namespace Nishchay\Security\Encrypt;

use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * CallbackEncrypter class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class CallbackEncrypter
{

    use MethodInvokerTrait;

    /**
     * Supported encryption types.
     */
    const SUPPORTED = ['php', 'db'];

    /**
     * Config.
     * 
     * @var \stdClass 
     */
    private $config;

    /**
     * Instance of query.
     * 
     * @var \Nishchay\Data\Query 
     */
    private $query;

    /**
     * 
     * @param type $config
     * @param \Nishchay\Data\Query $query
     */
    public function __construct($config, $query)
    {
        $this->query = $query;
        $this->setConfig($config);
    }

    /**
     * Sets config.
     * 
     * @param type $config
     * @throws ApplicationException
     * @throws NotSupportedException
     */
    private function setConfig($config)
    {
        if (!isset($config->type)) {
            throw new ApplicationException('Database encryption type is missing.', null, null, 927001);
        }

        if (!in_array($config->type, self::SUPPORTED)) {
            throw new NotSupportedException('Database encryption type [' .
                    $config->type . '] is not supported.', null, null, 927002);
        }
        $this->config = $config;
    }

    /**
     * Returns config for callback.
     * 
     * @param string $mode
     * @return \stdClass
     */
    private function getCallabackConfig($mode)
    {
        return $this->config->callback->{$mode};
    }

    /**
     * Returns parameter as string for DB function encryption and decryption.
     * 
     * @param array $params
     * @return \stdClass
     */
    private function getParameterAsString($params, $encrypt = true)
    {
        $placeholders = [];
        foreach ($params as $key => $value) {
            if ($encrypt === false && $key === 0) {
                $placeholders[] = $value;
                continue;
            }
            $placeholders[] = $this->query->bindValue($value);
        }

        return implode(',', $placeholders);
    }

    /**
     * Encrypts value.
     * 
     * @param string $string
     * @return string
     */
    public function encrypt($string)
    {
        $callback = $this->getCallabackConfig('encrypt');
        $params = array_merge([$string, $this->config->key], $callback->parameters);
        if ($this->config->type === 'php') {
            return $this->invokeMethod($callback->function, $params);
        }

        return $callback->function . '(' . $this->getParameterAsString($params) . ')';
    }

    /**
     * Decrypts value.
     * 
     * @param string $encrypted
     * @return string
     */
    public function decrypt($encrypted)
    {
        $callback = $this->getCallabackConfig('decrypt');
        $params = array_merge([$encrypted, $this->config->key], $callback->parameters);
        if ($this->config->type === 'php') {
            return $this->invokeMethod($callback->function, $params);
        }

        return $callback->function . '(' . $this->getParameterAsString($params, false) . ')';
    }

}
