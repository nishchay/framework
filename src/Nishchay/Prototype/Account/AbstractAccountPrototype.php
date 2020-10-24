<?php

namespace Nishchay\Prototype\Account;

use Nishchay\Data\EntityQuery;
use Nishchay\Prototype\AbstractPrototype;
use Nishchay\Prototype\Account\Response\LoginResponse;
use Nishchay\Session\Session;
use Nishchay\OAuth2\OAuth2;

/** Abstract account prototype class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
abstract class AbstractAccountPrototype extends AbstractPrototype
{

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
     * @return LoginResponse
     */
    protected function validateForm()
    {
        $response = parent::validateForm();

        if (is_array($response)) {
            return $this->getInstance(LoginResponse::class, [$response]);
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
    public function setSession(bool $session): self
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
    protected function writeSession(int $userId): self
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
    public function generateOAuth2(bool $flag): self
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
        return $this->getInstance(OAuth2::class)->generateUserCredentialToken($userId);
    }

}