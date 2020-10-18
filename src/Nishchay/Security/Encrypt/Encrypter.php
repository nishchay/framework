<?php

namespace Nishchay\Security\Encrypt;

/**
 * Encrypter class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Encrypter
{

    /**
     * Config name where encryption configuration is defined.
     */
    const CONFIG_NAME = 'encryption';

    /**
     * Cipher name.
     * 
     * @var string 
     */
    private $cipher;

    /**
     * Encryption key.
     * 
     * @var string 
     */
    private $key;

    /**
     * 
     * @param \stdClass $config
     */
    public function __construct($config)
    {
        $this->cipher = $config->cipher;
        $this->key = $config->key;
    }

    /**
     * Encrypts value.
     * 
     * @param type $value
     * @return type
     */
    public function encrypt($value)
    {
        $iv = $this->getIV();
        $encrypted = openssl_encrypt($value, $this->cipher, $this->key, 0, $iv);
        $payload = ['iv' => base64_encode($iv), 'encrypted' => $encrypted];
        return base64_encode(json_encode($payload));
    }

    /**
     * Decrypts value.
     * 
     * @param type $encrypted
     * @return type
     */
    public function decrypt($encrypted)
    {
        $payload = $this->getPayload($encrypted);
        if ($payload === false) {
            return null;
        }

        return openssl_decrypt($payload->encrypted, $this->cipher, $this->key, 0, base64_decode($payload->iv));
    }

    /**
     * Returns payload from encrypted string.
     * 
     * @param string $payloadString
     * @return boolean|\stdClass
     */
    private function getPayload($payloadString)
    {
        $payload = json_decode(base64_decode($payloadString));
        if (!isset($payload->iv) ||
                !isset($payload->encrypted)) {
            return false;
        }

        return $payload;
    }

    /**
     * Returns IV.
     * 
     * @return string
     */
    private function getIV()
    {
        $length = openssl_cipher_iv_length($this->cipher);

        if ($length === 0) {
            return '';
        }

        return openssl_random_pseudo_bytes($length);
    }

    /**
     * Returns encryption type.
     * 
     * @return string
     */
    public function getType()
    {
        return null;
    }

}
