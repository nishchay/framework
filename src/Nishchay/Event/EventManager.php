<?php

namespace Nishchay\Event;

use Nishchay;
use Nishchay\Exception\NotSupportedException;
use Nishchay\DI\DI;
use Nishchay\Event\EventMethod;
use Nishchay\Controller\ControllerClass;
use Nishchay\Controller\ControllerMethod;
use Nishchay\Attributes\Event\EventConfig;

/**
 * Event Manager class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class EventManager
{

    /**
     * Calling dependency injection.
     * 
     * @var \Nishchay\DI\DI
     */
    private $di;

    /**
     * Init
     */
    public function __construct()
    {
        $this->di = new DI(new \stdClass);
    }

    /**
     * Returns order in which event should be called.
     *  
     * @param   object      $attribute
     * @return  array
     */
    private function getOrder($attribute)
    {
        return $attribute === null ? [] : $attribute->getOrder();
    }

    /**
     * Returns callback method detail defined under attribute.
     * 
     * @param   object          $attribute
     * @return  array|boolean
     */
    private function getCallback($attribute)
    {
        return $attribute === null ? false : $attribute->getCallback();
    }

    /**
     * Fires event to be execute before calling route.
     * 
     * @param   \Nishchay\Controller\ControllerClass    $controller
     * @param   \Nishchay\Controller\ControllerMethod   $method
     * @return  mixed
     */
    public function fireBeforeEvent(ControllerClass $controller,
            ControllerMethod $method, $context, $scope)
    {
        $beforeController = $controller->getBeforeEvent();
        $beforeMethod = $method->getBeforeEvent();
        $response = $this->executeCallback($beforeController, $beforeMethod);

        if ($response === true) {
            $order = array_merge($this->getOrder($beforeMethod),
                    $this->getOrder($beforeController));
            $response = $this->fireEvent(Nishchay::getEventCollection()
                            ->getEvents(EventConfig::BEFORE, $context, $scope, $order));
        }

        return $response;
    }

    /**
     * Fires event to be execute after called route.
     * 
     * @param   \Nishchay\Controller\ControllerClass    $controller
     * @param   \Nishchay\Controller\ControllerMethod   $method
     * @return  mixed
     */
    public function fireAfterEvent(ControllerClass $controller,
            ControllerMethod $method, $context, $scope)
    {
        $afterController = $controller->getAfterEvent();
        $afterMethod = $method->getAfterEvent();
        $response = $this->executeCallback($afterController, $afterMethod);
        if ($response === true) {
            $order = array_merge($this->getOrder($afterMethod),
                    $this->getOrder($afterController));
            $response = $this->fireEvent(Nishchay::getEventCollection()
                            ->getEvents(EventConfig::AFTER, $context, $scope, $order));
        }
        return $response;
    }

    /**
     * Fires callback defined on controller and/or method.
     * 
     * @param   object      $controller
     * @param   object      $method
     * @return  mixed
     */
    private function executeCallback($controller, $method)
    {
        $response = $this->fire($controller, $this->getCallback($controller));
        return $response ? $this->fire($method, $this->getCallback($method)) : $response;
    }

    /**
     * Fires event.
     * 
     * @param instance $attribute
     * @param array $callback
     * @return boolean
     */
    private function fire($attribute, $callback)
    {
        if ($callback === false) {
            return true;
        }
        if (!($attribute->getOnce() && $attribute->isFired())) {
            $attribute->markFired();
            return $this->invokeCallback($callback, $attribute->getClass());
        }
        return true;
    }

    /**
     * Fires event.
     * 
     * @param   array       $events
     * @return  mixed
     */
    private function fireEvent($events)
    {
        foreach ($events as $attribute) {
            if ($attribute instanceof EventMethod &&
                    $attribute->getEventConfig()->getOnce() &&
                    $attribute->isFired()) {
                continue;
            }

            $response = $this->di
                    ->invoke($this->getInstance($attribute->getClass()),
                    $attribute->getMethod());
            $attribute instanceof EventMethod && $attribute->markFired();
            if ($response !== true) {
                return $response;
            }
        }
        return true;
    }

    /**
     * Invokes callback method.
     * 
     * @param   array       $callback
     * @return  mixed
     */
    private function invokeCallback($callback, $class)
    {
        # We allow callback of class where attribute is defined or registered
        # event class.
        if ($callback[0] !== $class && Nishchay::getEventCollection()->isExist($callback[0]) === false) {
            throw new NotSupportedException('Invalid event callback [' .
                            implode('::', $callback) . '].'
                            . ' It should belongs to controller or any event class.',
                            $class, null, 916009);
        }

        return $this->di->invoke($this->getInstance($callback[0]), $callback[1]);
    }

    /**
     * Returns instance of given class.
     * 
     * @param   string      $class
     * @return  object
     */
    private function getInstance($class)
    {
        if (($object = $this->di->getInstance($class)) !== false) {
            return $object;
        }

        return $this->di->create($class, true);
    }

}
