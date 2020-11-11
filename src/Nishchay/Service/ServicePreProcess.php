<?php

namespace Nishchay\Service;

use Nishchay;
use Processor;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\BadRequestException;
use Nishchay\Exception\AuthorizationFailedException;
use Nishchay\Controller\Annotation\Method\Method;
use Nishchay\Http\Request\Request;
use Nishchay\Utility\MethodInvokerTrait;
use Nishchay\OAuth2\OAuth2;

/**
 * Web Service process class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ServicePreProcess extends BaseServiceProcess
{

    use MethodInvokerTrait;

    /**
     *
     * @var type 
     */
    private static $payload;

    /**
     *
     * @var \Nishchay\Controller\Annotation\Method\Method 
     */
    protected $method;

    /**
     * 
     * @param Method $method
     */
    public function check(Method $method)
    {
        $this->init($method)
                ->verifyToken()
                ->verifyFieldsDemand();
    }

    /**
     * Verify service token.
     * 
     * @return $this
     * @throws AuthorizationFailedException
     */
    private function verifyToken()
    {
        if ($this->service === false || $this->service->getToken() === false) {
            return $this;
        }

        $verifyCallback = Nishchay::getSetting('service.token.verifyCallback');

        $isValid = true;
        $errroCode = 0;
        if ($verifyCallback === 'oauth') {
            if ((self::$payload = OAuth2::getInstance()->verify($this->getRequestToken())) === false) {
                list($isValid, $errroCode) = [false, 928011];
            }
        } else if ($verifyCallback instanceof \Closure) {
            if ($this->invokeMethod($verifyCallback, [$this->getRequestToken()]) !== true) {
                list($isValid, $errroCode) = [false, 928012];
            }
        } else {
            if ($this->getSessionToken() !== $this->getRequestToken()) {
                list($isValid, $errroCode) = [false, 928004];
            }
        }

        if ($isValid === false) {
            throw new AuthorizationFailedException('Invalid service token.', null, null, $errroCode);
        }

        return $this;
    }

    /**
     * Returns token value from session.
     * 
     * @return string
     * @throws AuthorizationFailedException
     */
    private function getSessionToken()
    {
        $token = Processor::getInternalSessionValue(
                        Nishchay::getSetting('service.token.sessionName')
        );

        if ($token !== false) {
            return $token;
        }
        throw new AuthorizationFailedException('This service requires'
                . ' [token] to access which not been created, please set'
                . ' token and then use it to access.', null, null, 928005);
    }

    /**
     * 
     * @throws AuthorizationFailedException
     */
    private function getRequestToken()
    {
        $where = strtoupper(Nishchay::getSetting('service.token.where'));
        if (!in_array(strtoupper($where), [Request::HEADER, Request::GET, Request::POST])) {
            throw new NotSupportedException('Invalid token location [' . $where . '].', null, null, 928006);
        }
        $name = Nishchay::getSetting('service.token.name');
        $token = $where === Request::HEADER ?
                $this->getFromHeader($name) :
                $this->getTokenFromRequestParameter($where, $name);

        if ($token === false) {
            throw new AuthorizationFailedException('This service requires'
                    . ' [token].', null, null, 928007);
        }
        return $token;
    }

    /**
     * Returns request token from GET or POST parameter. This is based
     * {service.token.where} service setting.
     * 
     * @param string $where
     * @param string $name
     * @return stirng
     */
    private function getTokenFromRequestParameter($where, $name)
    {
        if ($where === Request::POST) {
            return Request::post($name);
        }

        return Request::get($name);
    }

    /**
     * Returns token from request header.
     * 
     * @param string $name
     * @return string
     */
    private function getFromHeader($name)
    {
        return Request::server('HTTP_' .
                        strtoupper(str_replace('-', '_', $name)));
    }

    /**
     * Initialization.
     * 
     * @param Method $method
     */
    private function init(Method $method)
    {
        $this->setMethod($method)
                ->setService($this->method->getService())
                ->setFields()
                ->invokeBeforeHook();
        return $this;
    }

    /**
     * Invokes hooks which need to called before any service checks are made.
     * 
     * @return $this
     */
    private function invokeBeforeHook()
    {
        $before = Nishchay::getSetting('service.event.before');
        if ($before !== false) {
            $this->invokeMethod($before);
        }
        return $this;
    }

    /**
     * Sets method.
     * 
     * @param Method $method
     * @return $this
     */
    private function setMethod(Method $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Verifies fields demand of request.
     * 
     * @return $this
     * @throws BadRequestException
     */
    private function verifyFieldsDemand()
    {
        if ($this->service === false) {
            return $this;
        }
        $fields = $this->service->getFields();

        # If fields is NULL means client must demand for fields they need.
        if ($fields === null && $this->fields === false) {
            throw new BadRequestException('GET parameter [fields]'
                    . ' is required.', $this->service->getClass(), $this->service->getMethod(), 928008);
        }

        # For fields = false there should not be demand for fields they want.
        if ($fields === false && $this->fields !== false) {
            throw new BadRequestException('GET parameter [fields] is not'
                    . ' supported as service does not allow response'
                    . ' filtering.', $this->service->getClass(), $this->service->getMethod(), 928009);
        }

        # If @Service annotation has defined supported fields, we should check
        # for fields demanded are valid or not.
        if ($this->service->getSupported() !== false && $this->fields !== false) {
            $diff = array_diff($this->fields, $this->service->getSupported());
            if (!empty($diff)) {
                throw new BadRequestException('Fields [' . implode(',', $diff) .
                        '] requested is not supported by this service.', null, null, 928010);
            }
        }

        return $this;
    }

    /**
     * Returns payload of OAuth2 token.
     * 
     * @return type
     */
    public static function getPayload()
    {
        return self::$payload;
    }

}
