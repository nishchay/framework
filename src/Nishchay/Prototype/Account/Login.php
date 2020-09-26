<?php

namespace Nishchay\Prototype\Account;

use Nishchay\Exception\BadRequestException;
use Nishchay\Exception\AuthorizationFailedException;
use Nishchay\Data\EntityQuery;
use Nishchay\Session\Session;
use Nishchay\Security\Encrypt\EncryptTrait;
use Nishchay\Processor\FetchSingletonTrait;

/**
 * Login prototype class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Login
{

    use EncryptTrait,
        FetchSingletonTrait;

    /**
     * Entity class for login
     * @var string
     */
    private $entity;
    private $email;
    private $password;

    public function __construct(string $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Returns entity manager instance for the entity.
     * 
     * @return EntityManager
     */
    private function getEntityQuery(): EntityQuery
    {
        return $this->getInstance(EntityQuery::class)
                        ->setEntity($this->entity);
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
        return $this;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
        return $this;
    }

    public function execute()
    {
        $user = $this->getEntityQuery()
                ->setProperty('User')
                ->setCondition([
                    'email' => $this->getEncrypter()->encrypt($this->email)
                ])
                ->getOne();

        if ($user === false) {
            throw new BadRequestException('User does not exists.');
        }


        if ($user->isActive === false) {
            throw new AuthorizationFailedException('User is not active');
        }

        if ($user->isVerified === false) {
            throw new AuthorizationFailedException('User is not verified.');
        }

        if (password_verify($this->password, $user->password) === false) {
            throw new AuthorizationFailedException('Invalid password.');
        }

        $session = $this->getInstance(Session::class);
        $session->isLogged = true;
        $session->userId = $user->userId;

        return $user;
    }

}
