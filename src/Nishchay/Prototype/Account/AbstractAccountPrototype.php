<?php

namespace Nishchay\Prototype\Account;

use Nishchay;
use Nishchay\Data\EntityQuery;
use Nishchay\Prototype\AbstractPrototype;
use Nishchay\Prototype\Account\Response\AccountResponse;
use Nishchay\Session\Session;
use Nishchay\Http\Request\Request;
use Nishchay\Exception\BadRequestException;
use Closure;
use Nishchay\Utility\MethodInvokerTrait;

/** Abstract account prototype class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
abstract class AbstractAccountPrototype extends AbstractPrototype
{

    use MethodInvokerTrait;

    /**
     * Alias for entity.
     */
    const ENTITY_ALIAS = 'User';

    /**
     * Flag for writing userId to sessions.
     * 
     * @var bool
     */
    protected $session = false;

    /**
     * Whether to generate OAuth token.
     * 
     * @var bool
     */
    protected $oauth = true;

    /**
     * Flag for whether scope is required or not.
     * 
     * @var bool
     */
    protected $isScopeRequired = true;

    /**
     * Consider all scope defined in an application.
     * 
     * @var bool
     */
    protected $considerAllScope = false;

    /**
     * Callback for verifying scope.
     * 
     * @var \Closure
     */
    protected $verifyScope;

    /**
     * Returns entity query.
     * 
     * @return EntityQuery
     */
    protected function getEntityQuery(): EntityQuery
    {
        return $this->getInstance(EntityQuery::class)
                        ->setEntity($this->getEntityClass(), self::ENTITY_ALIAS);
    }

    /**
     * Validates form.
     * 
     * @return AccountResponse
     */
    protected function validateForm()
    {
        $response = parent::validateForm();

        if (is_array($response)) {
            return $this->getInstance(AccountResponse::class, [$response]);
        }
    }

    /**
     * Returns user detail.
     * 
     * @return \Nishchay\Data\EntityManager|null
     */
    protected function getUser(array $condition, string $emailName)
    {
        $query = $this->getEntityQuery()
                ->setProperty(self::ENTITY_ALIAS);

        $dataType = $this->getDataClass()
                ->getProperty($emailName)
                ->getProperty()
                ->getDatatype();

        # If email is encrypted.
        if (empty($condition)) {
            if ($dataType->getEncrypt()) {
                $encryption = $this->getEncrypter($query);
                $asItIs = $this->isDBEncryption() ? Query::AS_IT_IS : '';
                $condition[$emailName . $asItIs] = $encryption->encrypt($this->getEmail());
            } else {
                $condition[$emailName] = $this->getEmail();
            }
        }

        $query->setCondition($condition);

        return $query->getOne();
    }

    /**
     * Enable or disable writing userId and isLoggged= true to session.
     * 
     * @param array $session
     * @return \self
     */
    public function setSession(bool $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Write user is logged to session.
     * 
     * @param int $userId
     * @return \self
     */
    protected function writeSession(int $userId)
    {
        if ($this->session) {
            $session = $this->getInstance(Session::class);
            $session->isLogged = true;
            $session->userId = $userId;
        }

        return $this;
    }

    /**
     * Enable or disable generation of OAuth.
     * 
     * @param bool $flag
     * @return \self
     */
    public function generateOAuth2(bool $flag)
    {
        $this->oauth = $flag;
        return $this;
    }

    /**
     * Generates and returns OAuth token.
     * 
     * @param int $userId
     * @return array
     */
    protected function getAccessToken(int $userId)
    {
        if ($this->oauth === false) {
            return null;
        }

        $scope = Request::post('scope');

        if ($this->getScopeRequired() && $scope === false) {
            throw new BadRequestException(message: 'Please pass scope.',
                            code: 935007);
        }

        if ($scope !== false) {
            $scope = explode(',', $scope);
        } else {
            $scope = $this->getConsiderAllScope() ? Nishchay::getScopeCollection()->get() : null;
        }

        if (($callback = $this->getVerifyScope()) !== null && $this->invokeMethod($callback,
                        [$scope]) !== true) {
            throw new BadRequestException(message: 'Invalid scope.',
                            code: 935008);
        }

        return Nishchay::getOAuth2()->generateUserCredentialToken($userId,
                        $scope);
    }

    /**
     * Returns TRUE if scope is required.
     * 
     * @return bool
     */
    public function getScopeRequired(): bool
    {
        return $this->isScopeRequired;
    }

    /**
     * Set scope required to generate OAuth2 token.
     * 
     * @param bool $isScopeRequired
     * @return $this
     */
    public function setScopeRequired(bool $isScopeRequired)
    {
        $this->isScopeRequired = $isScopeRequired;
        return $this;
    }

    /**
     * Returns flag for considering all scope defined in an application.
     * @return bool
     */
    public function getConsiderAllScope(): bool
    {
        return $this->considerAllScope;
    }

    /**
     * Sets flag to consider all scope defined in an application.
     * 
     * @param bool $considerAllScope
     * @return void
     */
    public function setConsiderAllScope(bool $considerAllScope): self
    {
        $this->considerAllScope = $considerAllScope;
        return $this;
    }

    /**
     * Returns callback to verify scope.
     * 
     * @return Closure|null
     */
    public function getVerifyScope(): ?Closure
    {
        return $this->verifyScope;
    }

    /**
     * Sets callback to verify scope before generating oauth token.
     * Nishchay still verifies scope before generating oauth token apart from this callback.
     * 
     * @param Closure $verifyScope
     * @return $this
     */
    public function verifyScope(Closure $verifyScope)
    {
        $this->verifyScope = $verifyScope;
        return $this;
    }

}
