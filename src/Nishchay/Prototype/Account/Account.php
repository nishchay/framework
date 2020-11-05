<?php

namespace Nishchay\Prototype\Account;

use Nishchay\Processor\FetchSingletonTrait;

/**
 * Account prototype class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
class Account
{

    use FetchSingletonTrait;

    /**
     * Returns account login prototype.
     * 
     * @param string $entity
     * @return \Nishchay\Prototype\Account\Login
     */
    public function getLogin(string $entity): Login
    {
        return $this->getInstance(Login::class, [$entity]);
    }

    /**
     * Returns account register prototype.
     * 
     * @param string $entity
     * @return \Nishchay\Prototype\Account\Register
     */
    public function getRegister(string $entity): Register
    {
        return $this->getInstance(Register::class, [$entity]);
    }

}
