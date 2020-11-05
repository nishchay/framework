<?php

namespace Nishchay\Prototype\Account;

use Closure;
use Nishchay\Exception\BadRequestException;
use Nishchay\Prototype\Account\{
    AbstractAccountPrototype,
    Response\LoginResponse
};

/**
 * Register prototype class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
class Register extends AbstractAccountPrototype
{

    /**
     * Pre register callback.
     * 
     * @var Closure 
     */
    private $preRegister;

    /**
     * Post register callback.
     * 
     * @var Closure
     */
    private $postRegister;

    /**
     * Execute register prototype.
     * 
     * @return LoginResponse
     */
    public function execute(): LoginResponse
    {
        if (($response = $this->validateForm()) instanceof LoginResponse) {
            return $response;
        }

        $email = $this->getForm()->getEmail();

        if ($this->getUser([], $email->getName()) !== false) {
            throw new BadRequestException('Account already exists with'
                    . ' provided email.');
        }

        # Before registeration callback
        $this->getPreRegister() instanceof Closure &&
                call_user_func($this->getPreRegister(), [$this->getForm()]);

        $entity = $this->prepareEntity()
                ->getEntity();

        $userId = $this->saveEntity();

        # After registeration callback.
        $this->getPostRegister() instanceof Closure &&
                call_user_func($this->getPostRegister(), [$entity, $this->getForm()]);


        return $this->writeSession($userId)
                        ->getInstance(LoginResponse::class, [[
                        'userDetail' => $entity,
                        'accessToken' => $this->getAccessToken($userId),
                        'isSuccess' => true
        ]]);
    }

    /**
     * Returns pre register callback.
     * 
     * @return Closure|null
     */
    public function getPreRegister(): ?Closure
    {
        return $this->preRegister;
    }

    /**
     * Returns post register callback.
     * 
     * @return Closure|null
     */
    public function getPostRegister(): ?Closure
    {
        return $this->postRegister;
    }

    /**
     * Pre register callback.
     * 
     * @param Closure $preRegister
     * @return $this
     */
    public function preRegister(Closure $preRegister)
    {
        $this->preRegister = $preRegister;
        return $this;
    }

    /**
     * Post register callback.
     * 
     * @param Closure $postRegister
     * @return $this
     */
    public function postRegister(Closure $postRegister)
    {
        $this->postRegister = $postRegister;
        return $this;
    }

    /**
     * Returns submitted email.
     * 
     * @return string
     */
    protected function getEmail()
    {
        return $this->getForm()->getEmail()->getRequest();
    }

}
