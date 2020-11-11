<?php

namespace Nishchay\OAuth2;

use Nishchay;
use Processor;
use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\BadRequestException;
use Nishchay\Exception\AuthorizationFailedException;
use Nishchay\Utility\StringUtility;
use Nishchay\Processor\AbstractSingleton;

/**
 * OAuth2 class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class OAuth2 extends AbstractSingleton
{

    protected function onCreateInstance()
    {
        
    }

    /**
     * OAuth configuration name.
     */
    const CONFIG_NAME = 'service.token.oauth';

    /**
     * Separator for token.
     */
    const SEPARATOR = '.';

    /**
     * Version of this class.
     */
    const OAUTH_IMPLEMENTATON_VERSION = 1;

    /**
     * Return header.
     * 
     * @return string
     */
    private function getHeader(): string
    {
        return json_encode(['typ' => 'JWT', 'alg' => 'RS256']);
    }

    private function validateGenerateScope(?array $scope)
    {
        if ($scope === null) {
            return true;
        }

        foreach ($scope as $name) {
            if (Nishchay::getScopeCollection()->isExists($name) === false) {
                throw new BadRequestException('Invalid scope [' . $name . '].');
            }
        }

        return true;
    }

    /**
     * Returns payload.
     * 
     * @param type $userId
     * @param type $scope
     * @return type
     */
    private function getPayload($userId = null, $scope = null)
    {
        $this->validateGenerateScope($scope);
        $time = time();
        return json_encode([
            'oti' => StringUtility::getRandomString(32, true),
            'cb' => 'nishchay',
            'ov' => self::OAUTH_IMPLEMENTATON_VERSION,
            'appId' => $this->getAppId(),
            'uu' => $userId,
            'scope' => $scope,
            'ct' => $time,
            'exp' => $time + $this->getExpiry()
        ]);
    }

    /**
     * Generates token  and returns it.
     * 
     * @param int $userId
     * @return array
     */
    public function generateUserCredentialToken($userId, ?array $scope = null): array
    {
        $header = $this->urlSafeBase64Encode($this->getHeader());
        $payload = $this->urlSafeBase64Encode($this->getPayload($userId, $scope));
        openssl_sign($header . self::SEPARATOR . $payload . self::SEPARATOR . $this->getAppSecret(), $signature, $this->getPrivateKey(), OPENSSL_ALGO_SHA256);

        $signature = $this->urlSafeBase64Encode($signature);

        $response = [
            implode(self::SEPARATOR, [$header, $payload, $signature]),
            $this->getExpiry(),
            'password',
            empty($scope) ? null : implode(',', $scope)
        ];

        return array_combine(['accessToken', 'expiresIn', 'tokenType', 'scope'], $response);
    }

    /**
     * Encodes string to base 64.
     * 
     * @param string $data
     * @return string
     */
    public function urlSafeBase64Encode($data)
    {
        return str_replace(['+', '/', "\r", "\n", '='],
                ['-', '_'],
                base64_encode($data));
    }

    /**
     * Decodes base64 encoded string.
     * 
     * @param string $string
     * @return string
     */
    private function urlSafeBase64Decode($string)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
    }

    /**
     * Returns client Id.
     * 
     * @return string
     */
    private function getAppId(): string
    {
        return Nishchay::getSetting(self::CONFIG_NAME . '.credential.appId');
    }

    /**
     * Returns client secret.
     * 
     * @return string
     */
    private function getAppSecret(): string
    {
        return Nishchay::getSetting(self::CONFIG_NAME . '.credential.appSecret');
    }

    /**
     * Returns token expiry time.
     * 
     * @return int
     */
    private function getExpiry(): int
    {
        return (int) Nishchay::getSetting(self::CONFIG_NAME . '.expiry');
    }

    /**
     * Verifies token.
     * 
     * @param string $token
     * @return boolean
     */
    public function verify($token)
    {
        $token = explode(self::SEPARATOR, $token);
        if (count($token) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $token;
        $decodedSignature = $this->urlSafeBase64Decode($signature);


        $payloadToVerify = ($header . self::SEPARATOR . $payload . self::SEPARATOR . $this->getAppSecret());

        $verified = openssl_verify($payloadToVerify, $decodedSignature, $this->getPublicKey(), OPENSSL_ALGO_SHA256);

        # Invalid token
        if ($verified !== 1) {
            return false;
        }

        # Let' now check token is expired or not and scope.
        $payload = json_decode($this->urlSafeBase64Decode($payload));

        # Payload is not json
        if (!$payload) {
            return false;
        }

        # Checking expiry time
        if ($payload->exp < time()) {
            return false;
        }

        $this->isValidScope($payload);

        return $payload;
    }

    /**
     * Checks whether scope in access token is exists in current route scope.
     * 
     * @param \stdClass $payload
     * @return boolean
     * @throws AuthorizationFailedException
     */
    private function isValidScope($payload)
    {
        $currentScope = Processor::getStageDetail('scopeName');

        if ($currentScope === false) {
            return true;
        }

        if ($payload->scope !== null) {
            foreach ($payload->scope as $name) {
                if (in_array($name, $currentScope)) {
                    return true;
                }
            }
        }

        throw new AuthorizationFailedException('Unautorized access to service.');
    }

    /**
     * Returns private key.
     * 
     * @return string
     */
    private function getPrivateKey()
    {
        $path = Nishchay::getSetting(self::CONFIG_NAME . '.privateKey');

        if (file_exists($path) === false) {
            throw new ApplicationException('Private key does not exists.');
        }
        return file_get_contents($path);
    }

    /**
     * Returns public key.
     * 
     * @return string
     */
    private function getPublicKey()
    {
        $path = Nishchay::getSetting(self::CONFIG_NAME . '.publicKey');

        if (file_exists($path) === false) {
            throw new ApplicationException('Public key does not exists.');
        }

        return file_get_contents($path);
    }

}
