<?php

namespace Nishchay\OAuth2;

use Nishchay;
use Nishchay\Utility\StringUtility;

/**
 * Description of OAuth2
 *
 * @author bhavik
 */
class OAuth2
{

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

    /**
     * Returns payload.
     * 
     * @param type $userId
     * @param type $scope
     * @return type
     */
    private function getPayload($userId = null, $scope = null)
    {
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
    public function generateUserCredentialToken($userId): array
    {
        $header = $this->urlSafeBase64Encode($this->getHeader());
        $payload = $this->urlSafeBase64Encode($this->getPayload($userId));
        openssl_sign($header . self::SEPARATOR . $payload, $signature, $this->getPrivateKey(), OPENSSL_ALGO_SHA256);

        $signature = $this->urlSafeBase64Encode($signature);

        $response = [
            implode(self::SEPARATOR, [$header, $payload, $signature]),
            $this->getExpiry(),
            'password',
            null
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


        $payloadToVerify = ($header . self::SEPARATOR . $payload);

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

        return true;
    }

    /**
     * Returns private key.
     * 
     * @return string
     */
    private function getPrivateKey()
    {
        $privateKey = Nishchay::getSetting(self::CONFIG_NAME . '.privateKey');
        return file_get_contents($privateKey);
    }

    /**
     * Returns public key.
     * 
     * @return string
     */
    private function getPublicKey()
    {
        $privateKey = Nishchay::getSetting(self::CONFIG_NAME . '.publicKey');
        return file_get_contents($privateKey);
    }

}
