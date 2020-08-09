<?php

namespace Nishchay\Controller;

use Processor;
use ReflectionClass;
use Nishchay\Http\Request\Request;
use Nishchay\Http\Request\RequestStore;
use Nishchay\Http\Request\RequestForwarder;

/**
 * Description of Forward
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Forwarder
{

    /**
     * Request forwarded to route.
     * 
     * 
     * @var string 
     */
    private $forwarded;

    /**
     * Stage number form which request is forwarded.
     * 
     * @var type 
     */
    private $stage;

    /**
     * All request store value of the forwarding route.
     * 
     * @var array 
     */
    private $requestStore = [];

    /**
     * Stage detail of forwarding route.
     * 
     * @var array 
     */
    private $stageDetail = [];

    /**
     * Instance to Request forwarder class.
     * 
     * @var \Nishchay\Http\RequestForwarder 
     */
    private $requestForwarder;

    /**
     * Request methods.
     * 
     * @var array 
     */
    private $parameterType = ['GET', 'POST'];

    /**
     * 
     * @param RequestForwarder $requestForwarder
     */
    public function __construct(RequestForwarder $requestForwarder)
    {
        $this->forwarded = $requestForwarder->getRoute();
        $this->requestForwarder = $requestForwarder;
        $this->stage = Processor::stage();
        $this->stageDetail = Processor::getStageDetail();
        $this->requestStore = RequestStore::getAll();
        $this->prepare();
    }

    /**
     * Preparing request so the details are available to route.
     */
    private function prepare()
    {
        # Flushing request store when forwarding route want to flush all
        # added value.
        if ($this->requestForwarder->isForwardingWithFlushRequestStore()) {
            $reflection = new ReflectionClass(RequestStore::class);
            $property = $reflection->getProperty(RequestStore::STORE_PROPERTY);
            $property->setAccessible(true);
            $property->setValue([]);
        }

        # Let's make it real request by giving all added get and post parameter.
        $requestReflection = new ReflectionClass(Request::class);

        $instanceProperty = $requestReflection
                ->getProperty(Request::INSTANCE_PROPERTY);
        $instanceProperty->setAccessible(true);
        $instance = $instanceProperty->getValue();
        $this->setParameter($requestReflection, $instance);
        $this->setMethodType($requestReflection, $instance);
    }

    /**
     * Sets GET and POST parameter.
     * 
     * @param \ReflectionClass $requestReflection
     * @param \Nishchay\Http\Request\Request $instance
     */
    private function setParameter($requestReflection, $instance)
    {
        # Updating GET and POST value only.
        foreach ($this->parameterType as $type) {
            if ($type === 'POST' &&
                            $this->requestForwarder
                            ->getForwardingTypeParameter() !== 'POST') {
                continue;
            }
            $property = $requestReflection->getProperty('_' . $type);
            $property->setAccessible(true);
            $this->setValue($instance, $property, $this->getValue($type));
        }
    }

    /**
     * Sets request method type.
     * 
     * @param \ReflectionClass $reflection
     * @param \Nishchay\Http\Request\Request $instance
     */
    private function setMethodType($reflection, $instance)
    {
        $property = $reflection->getProperty('_SERVER');
        $property->setAccessible(true);
        $existing = $property->getValue($instance);
        $existing['REQUEST_METHOD'] = $this->getValue('type');
        $this->setValue($instance, $property, $existing);
    }

    /**
     * 
     * @param type $name
     * @return type
     */
    private function getValue($name)
    {
        return call_user_func([
            $this->requestForwarder,
            'getForwarding' . ucfirst(strtolower($name)) . 'Parameter'
        ]);
    }

    /**
     * 
     * @param type $instance
     * @param type $property
     * @param type $value
     */
    private function setValue($instance, $property, $value)
    {
        $property->setValue($instance, $value);
    }

    /**
     * Returns route where the request is forwarded.
     * 
     * @return stirng
     */
    public function getForwardedRoute()
    {
        return $this->forwarded;
    }

    /**
     * Get request store value of added by forwarding route.
     * 
     * @param   string      $name
     * @return  mixed
     */
    public function getRequestStoreValue(string $name)
    {
        if (!isset($this->requestStore[$name])) {
            return false;
        }

        return $this->requestStore[$name];
    }

    /**
     * Returns stage info.
     * 
     * @param   string      $name
     * @return  mixed
     */
    public function getStageDetail($name)
    {
        return isset($this->stageDetail[$name]) ?
                $this->stageDetail[$name] : false;
    }

}
