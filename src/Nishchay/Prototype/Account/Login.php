<?php

namespace Nishchay\Prototype\Account;

use Nishchay\Exception\BadRequestException;
use Nishchay\Exception\AuthorizationFailedException;
use Closure;
use Nishchay\Data\EntityQuery;
use Nishchay\Session\Session;
use Nishchay\Security\Encrypt\EncryptTrait;
use Nishchay\Processor\FetchSingletonTrait;
use Nishchay\Http\Request\Request;
use Nishchay\Http\Response\Response;
use Nishchay\OAuth2\OAuth2;
use Nishchay\Prototype\Account\Response\LoginResponse;
use Nishchay\Data\Reflection\DataClass;
use Nishchay\Data\Query;

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

    const ENTITY_ALIAS = 'User';

    /**
     * Entity class for login
     * 
     * @var string
     */
    private $entity;

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
     * Form class name.
     * 
     * @var string
     */
    private $form;

    /**
     * Whether to generate oauth token.
     * 
     * @var bool
     */
    private $oauth = true;

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
     * Flag for writing userId to sessions.
     * 
     * @var bool
     */
    private $session = false;

    /**
     * Post login closure.
     * 
     * @var Closure
     */
    private $postLogin;

    /**
     * 
     * @param string $entity
     */
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
                        ->setEntity($this->entity, self::ENTITY_ALIAS);
    }

    /**
     * 
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
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
     * 
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
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
     * Sets form class name.
     * 
     * @param string $class
     * @return $this
     */
    public function setForm(string $class): self
    {
        $this->form = $class;
        return $this;
    }

    /**
     * 
     * @return \Nishchay\Form\Form
     */
    private function getForm()
    {
        if ($this->form === null) {
            return null;
        }
        return $this->getInstance($this->form);
    }

    /**
     * 
     * @param bool $flag
     */
    public function setOAuth2(bool $flag): self
    {
        $this->oauth = $flag;
        return $this;
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
    public function setEmailName(string $emailName): self
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
    public function setPasswordName(string $passwordName): self
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
    public function verifyPassword(Closure $verifyPassword): self
    {
        $this->verifyPassword = $verifyPassword;
        return $this;
    }

    /**
     * 
     */
    public function getPostLogin(): Closure
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
     * Returns instance of data class for entity.
     * 
     * @return DataClass
     */
    private function getDataClass(): DataClass
    {
        return $this->getInstance(DataClass::class, [$this->entity]);
    }

    /**
     * Returns user detail.
     * 
     * @return \Nishchay\Data\EntityManager|null
     */
    private function getUser()
    {
        $query = $this->getEntityQuery()
                ->setProperty(self::ENTITY_ALIAS);

        $dataType = $this->getDataClass()
                ->getProperty($this->getEmailName())
                ->getProperty()
                ->getDatatype();

        # If email is encrypted.
        if (empty($this->condition)) {
            if ($dataType->getEncrypt()) {
                $encryption = $this->getEncrypter($query);
                $asItIs = $this->isDBEncryption() ? Query::AS_IT_IS : '';
                $this->condition[$this->getEmailName() . $asItIs] = $encryption->encrypt($this->getEmail());
            } else {
                $this->condition[$this->getEmailName()] = $this->getEmail();
            }
        }

        $query->setCondition($this->condition);

        return $query->getOne();
    }

    /**
     * Sets query conditions to be use to fetch user.
     * 
     * @param array $condition
     * @return $this
     */
    public function setCondition(array $condition): self
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * Enable or disable writing userId and isLoggged= true to session.
     * 
     * @param array $session
     * @return $this
     */
    public function setSession(bool $session): self
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Validates form.
     * 
     * @return LoginResponse|null
     */
    private function validateForm(): ?LoginResponse
    {
        if (($form = $this->getForm()) !== null) {
            if ($form->validate() !== true) {
                Response::setStatus(HTTP_STATUS_BAD_REQUEST);
                $fields = ['errors' => $form->getErrors()];
                if ($form->getCSRF() !== false) {
                    $fields['csrf'] = $form->getCSRF()->getValue();
                }
                return $this->getInstance(LoginResponse::class, [$fields]);
            }
        }

        return null;
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

        if ((($user = $this->getUser())) === false) {
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
    private function isUserActive($user): self
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
    private function isUserVerified($user): self
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

    /**
     * Write user is logged to session.
     * 
     * @param int $userId
     * @return $this
     */
    private function writeSession(int $userId): self
    {
        if ($this->session) {
            $session = $this->getInstance(Session::class);
            $session->isLogged = true;
            $session->userId = $userId;
        }

        return $this;
    }

    /**
     * Generates and returns OAuth token.
     * 
     * @param int $userId
     * @return array
     */
    private function getAccessToken(int $userId)
    {
        if ($this->oauth === false) {
            return null;
        }
        return $this->getInstance(OAuth2::class)->generateUserCredentialToken($userId);
    }

}
