<?php

namespace Nishchay\Prototype\Account\Response;

/**
 * Login response class account login and register prototype.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
class LoginResponse
{

    /**
     *
     * @var type 
     */
    private $userDetail;

    /**
     *
     * @var type 
     */
    private $accessToken;

    /**
     * Flag for whether user login succeeded.
     * 
     * @var bool 
     */
    private $isSuccess = false;

    /**
     * Validation errors if validation form has been used and it failed.
     * 
     * @var type 
     */
    private $errors = [];

    /**
     * CSRF token.
     * 
     * @var string
     */
    private $csrf;

    public function __construct(array $fields)
    {
        foreach ($fields as $name => $value) {
            if (property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * Returns user detail.
     * 
     * @return type
     */
    public function getUserDetail()
    {
        return $this->userDetail;
    }

    /**
     * Returns access token.
     * 
     * @return type
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * 
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * Returns validation errors.
     * Returns empty if validation has been passed.
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns csrf token.
     * 
     * @return string
     */
    public function getCsrf(): string
    {
        return $this->csrf;
    }

}
