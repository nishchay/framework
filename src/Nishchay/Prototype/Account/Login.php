<?php

namespace Nishchay\Prototype\Account;

use Closure;
use Nishchay\Exception\BadRequestException;
use Nishchay\Exception\AuthorizationFailedException;
use Nishchay\Http\Request\Request;
use Nishchay\Prototype\Account\Response\LoginResponse;
use Nishchay\Prototype\Account\AbstractAccountPrototype;

/**
 * Login prototype class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
class Login extends AbstractAccountPrototype
{

    /**
     * User email.
     * 
     * @var string
     */
    private $email;

    /**
     * Email field name.
     * 
     * @var string
     */
    private $emailName = 'email';

    /**
     * User password.
     * 
     * @var string
     */
    private $password;

    /**
     * Password field name.
     * 
     * @var string 
     */
    private $passwordName = 'password';

    /**
     * Password verification callback.
     * 
     * @var Closure
     */
    private $verifyPassword;

    /**
     * Conditions to fetch user.
     * 
     * @var array
     */
    private $condition = [];

    /**
     * Post login closure.
     * 
     * @var Closure
     */
    private $postLogin;

    /**
     * Sets user's email/username. While executing login prototype find user with
     * this email/username.
     * 
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Returns email from request.
     * 
     * @return string
     */
    public function getEmail()
    {
        if ($this->email) {
            return $this->email;
        }

        if ($this->getForm()) {
            return $this->getForm()->getEmail()->getRequest();
        }

        return Request::post($this->emailName);
    }

    /**
     * Set user password. While executing login this password will be matched
     * against user's actual password.
     * 
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Returns password from request.
     * 
     * @return string
     */
    public function getPassword()
    {
        if ($this->password) {
            return $this->password;
        }

        if ($this->getForm()) {
            return $this->getForm()->getPassword()->getRequest();
        }

        return Request::post($this->passwordName);
    }

    /**
     * Returns email field name.
     * 
     * @return string
     */
    public function getEmailName(): string
    {
        return $this->emailName;
    }

    /**
     * Returns password field name.
     * 
     * @return string
     */
    public function getPasswordName(): string
    {
        return $this->passwordName;
    }

    /**
     * Returns password verification closure.
     * 
     * @return Closure|null
     */
    public function getVerifyPassword(): ?Closure
    {
        return $this->verifyPassword;
    }

    /**
     * Sets email field name.
     * 
     * @param string $emailName
     * @return $this
     */
    public function setEmailName(string $emailName)
    {
        $this->emailName = $emailName;
        return $this;
    }

    /**
     * Sets password field name.
     * 
     * @param string $passwordName
     * @return $this
     */
    public function setPasswordName(string $passwordName)
    {
        $this->passwordName = $passwordName;
        return $this;
    }

    /**
     * Sets password verification callback.
     * 
     * @param Closure $verifyPassword
     * @return $this
     */
    public function verifyPassword(Closure $verifyPassword)
    {
        $this->verifyPassword = $verifyPassword;
        return $this;
    }

    /**
     * 
     */
    public function getPostLogin(): ?Closure
    {
        return $this->postLogin;
    }

    /**
     * 
     * @param Closure $postLogin
     * @return $this
     */
    public function postLogin(Closure $postLogin)
    {
        $this->postLogin = $postLogin;
        return $this;
    }

    /**
     * Sets query conditions to be use to fetch user.
     * 
     * @param array $condition
     * @return $this
     */
    public function setCondition(array $condition)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * Executes prototype to make user login.
     * 
     * @return LoginResponse
     * @throws BadRequestException
     * @throws AuthorizationFailedException
     */
    public function execute(): LoginResponse
    {
        if (($response = $this->validateForm()) instanceof LoginResponse) {
            return $response;
        }

        if ((($user = $this->getUser($this->condition, $this->getEmailName()))) === false) {
            throw new BadRequestException('User does not exists.');
        }

        $this->isUserActive($user)
                ->isUserVerified($user)
                ->processPasswordVerification($user->password);

        if ($this->getPostLogin() instanceof Closure) {
            call_user_func_array($this->getPostLogin(), [$user]);
        }

        $identity = $this->getDataClass()->getIdentity();

        return $this->writeSession($user->{$identity})
                        ->getInstance(LoginResponse::class, [[
                        'userDetail' => $user,
                        'accessToken' => $this->getAccessToken($user->{$identity}),
                        'isSuccess' => true
        ]]);
    }

    /**
     * Checks if user is active.
     * 
     * @param \Nishchay\Data\EntityManager $user
     * @return $this
     * @throws AuthorizationFailedException
     */
    private function isUserActive($user)
    {
        if (isset($user->isActive) && $user->isActive === false) {
            throw new AuthorizationFailedException('User is not active');
        }

        return $this;
    }

    /**
     * Check if user is verified.
     * 
     * @param \Nishchay\Data\EntityManager $user
     * @return $this
     * @throws AuthorizationFailedException
     */
    private function isUserVerified($user)
    {
        if (isset($user->isVerified) && $user->isVerified === false) {
            throw new AuthorizationFailedException('User is not verified.');
        }

        return $this;
    }

    /**
     * Verifies user password.
     * 
     * @param string $userPassword
     * @throws AuthorizationFailedException
     */
    private function processPasswordVerification($userPassword)
    {
        if ($this->getVerifyPassword() instanceof Closure) {
            if (call_user_func_array($this->getVerifyPassword(), [$this->getPassword(), $userPassword]) !== true) {
                throw new AuthorizationFailedException('Invalid password.');
            }
        } else if (password_verify($this->getPassword(), $userPassword) === false) {
            throw new AuthorizationFailedException('Invalid password.');
        }
    }

}
