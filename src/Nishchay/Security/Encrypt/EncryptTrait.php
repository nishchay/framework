<?php

namespace Nishchay\Security\Encrypt;

use Nishchay;

/**
 * Encrypter class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
trait EncryptTrait
{

    /**
     * Returns instance of encrypter.
     * 
     * @return \Nishchay\Security\Encrypt\Encrypter
     */
    private function getEncrypter($query = null)
    {
        $db = Nishchay::getSetting('database.' . Encrypter::CONFIG_NAME);
        if ($db->type === null) {
            return Nishchay::getEncrypter($db->name);
        }
        return (new CallbackEncrypter($db, $query));
    }

    /**
     * Returns true if Nishchay encryption & decryption need to be used.
     * 
     * @return boolean
     */
    protected function isDBEncryption()
    {
        return Nishchay::getSetting('database.' . Encrypter::CONFIG_NAME) === 'db';
    }

}
