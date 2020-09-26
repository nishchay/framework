<?php

namespace Nishchay\Prototype\Account;

/**
 * Account prototype class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Account
{

    /**
     * 
     * @param string $entity
     * @return \Nishchay\Prototype\Account\Login
     */
    public function getLogin(string $entity): Login
    {
        return new Login($entity);
    }

}
