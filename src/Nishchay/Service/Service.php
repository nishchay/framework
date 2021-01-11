<?php

namespace Nishchay\Service;

use Nishchay;
use Processor;

/**
 * Service class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Service
{

    /**
     *
     * @var \Nishchay\Service\Need 
     */
    private $need;

    /**
     * Controller class name.
     * 
     * @var string 
     */
    private $class;

    /**
     * Controller method name.
     * 
     * @var string 
     */
    private $method;

    /**
     * 
     */
    public function __construct()
    {
        $object = Processor::getStageDetail('object');
        $this->class = $object->getClass();
        $this->method = $object->getMethod();
        $this->setNeed();
    }

    /**
     * Returns instance of service need.
     * 
     * @return type
     */
    public function getNeed()
    {
        return $this->need;
    }

    /**
     * Sets instance service need.
     * 
     * @return \Nishchay\Service\Need
     */
    private function setNeed()
    {
        $this->need = new Need(Nishchay::getControllerCollection()
                        ->getMethod($this->class . '::' . $this->method
        ));
    }

    /**
     * Sets token for service.
     * 
     * @param type $value
     */
    public function setToken($value)
    {
        Processor::setInternalSessionValue(
                Nishchay::getSetting('service.token.sessionName'), (string) $value
        );
    }

    /**
     * Returns service token.
     * 
     * @return string
     */
    public function getToken()
    {
        return Processor::getInternalSessionValue(
                        Nishchay::getSetting('service.token.sessionName')
        );
    }

    /**
     * Returns payload form OAuth2 token.
     * 
     * @return \stdClass
     */
    public function getPayload()
    {
        return ServicePreProcess::getPayload();
    }

    /**
     * Returns userId from OAuth2 token.
     * 
     * @return int|bool
     */
    public function getUserId()
    {
        $payload = $this->getPayload();

        return $payload->uu ?? false;
    }

    /**
     * Returns scope from OAuth2 token.
     * 
     * @return array|null
     */
    public function getScope(): ?array
    {
        $payload = $this->getPayload();

        return $payload->scope ?? null;
    }

}
