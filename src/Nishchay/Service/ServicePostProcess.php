<?php

namespace Nishchay\Service;

use Nishchay;
use Nishchay\Exception\BadRequestException;
use Nishchay\Service\Annotation\Service;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * Service post process class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ServicePostProcess extends BaseServiceProcess
{

    use MethodInvokerTrait;

    /**
     * Fields to be rendered.
     * 
     * @var array 
     */
    private $render = [];

    /**
     * 
     * @param Service $service
     * @param type $render
     */
    public function __construct(Service $service, $render)
    {
        $this->render = $render;
        $this->setService($service)
                ->setFields()
                ->refactorFieldsDemand();
    }

    /**
     * Removing invalid field demand.
     * 
     * @return $this
     */
    private function refactorFieldsDemand()
    {
        # fields = false means there's no field demand.
        if ($this->fields === false) {
            return $this;
        }

        $responseFields = array_keys($this->render);

        $supportedFields = $this->getSupported();
        if (empty($supportedFields)) {
            $serviceFields = $this->service->getFields();
            if (is_array($serviceFields) && count($serviceFields) > 0) {
                $supportedFields = $this->service->getFields();
            }
        }
        foreach ($this->fields as $fieldName) {
            if (!in_array($fieldName, $responseFields) &&
                    !in_array($fieldName, $supportedFields) &&
                    !in_array($fieldName, $this->service->getAlways())) {
                throw new BadRequestException('Field [' . $fieldName . ']'
                        . ' is not supported by this service.', null, null, 928003);
            }
        }
        return $this;
    }

    /**
     * Checks for fields for its validity.
     * 
     * @return array
     */
    public function check()
    {

        # Removing fields which are not supported as defiend in supported 
        # parameter of @Service annotation.
        $this->removeUnSupported($this->getSupported());

        # Adding fields which always need to be sent.
        $this->addRenderFields($this->service->getAlways());

        # We will first remove fields which are not present in requested.
        $requested = $this->fields !== false ? $this->fields :
                (
                empty($this->service->getFields()) ?
                [] : $this->service->getFields()
                );

        $this->removeUnSupported($requested === 'all' ? [] : $requested);

        # Adding requested fields which are not present.
        $this->addRenderFields($requested);

        return $this->applyCallback();
    }

    /**
     * Applies service callback for response if its there any.
     * 
     * @return array
     */
    private function applyCallback()
    {
        $callback = Nishchay::getSetting('service.event.after');
        if ($callback === false || $this->isCallbackExist($callback) === false) {
            return $this->render;
        }

        return $this->invokeMethod($callback, [$this->render, false]);
    }

    /**
     * Returns supported field names.
     * 
     * @return array
     */
    private function getSupported()
    {
        return $this->service->getSupported() ?
                $this->service->getSupported() : [];
    }

    /**
     * Removes fields which are not supported.
     * 
     * @return null
     */
    private function removeUnSupported($supported)
    {
        if (empty($supported)) {
            return false;
        }

        # Preventing 'always' fields to be removed.
        $supported = array_merge($supported, $this->service->getAlways());
        foreach (array_keys($this->render) as $fieldName) {
            if (!in_array($fieldName, $supported)) {
                unset($this->render[$fieldName]);
            }
        }
    }

    /**
     * Adds fields to render.
     * 
     * @param array $fields
     */
    public function addRenderFields($fields)
    {
        if (!is_array($fields)) {
            return false;
        }
        foreach ($fields as $fieldName) {
            if (!array_key_exists($fieldName, $this->render)) {
                $this->render[$fieldName] = Nishchay::getSetting('service.default');
            }
        }
    }

}
