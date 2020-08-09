<?php

namespace Nishchay\Event;

use Nishchay;
use Nishchay\Exception\NotSupportedException;
use Nishchay\DI\DI;
use Nishchay\Event\Annotation\Method\Method;
use Nishchay\Controller\Annotation\Controller;
use Nishchay\Controller\Annotation\Method\Method as ControllerMethod;
use Nishchay\Event\Annotation\Method\Fire;

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
     * @param   object      $annotation
     * @return  array
     */
    private function getOrder($annotation)
    {
        return $annotation === false ? [] : $annotation->getOrder();
    }

    /**
     * Returns callback method detail defined under annotation.
     * 
     * @param   object          $annotation
     * @return  array|boolean
     */
    private function getCallback($annotation)
    {
        return $annotation === false ? false : $annotation->getCallback();
    }

    /**
     * Fires event to be execute before calling route.
     * 
     * @param   \Nishchay\Controller\Annotation\Controller         $controller
     * @param   \Nishchay\Controller\Annotation\Method\Method      $method
     * @return  mixed
     */
    public function fireBeforeEvent(Controller $controller, ControllerMethod $method, $context, $scope)
    {
        $beforeController = $controller->getBeforeevent();
        $beforeMethod = $method->getBeforeevent();
        $response = $this->executeCallback($beforeController, $beforeMethod);

        if ($response === true) {
            $order = array_merge($this->getOrder($beforeMethod), $this->getOrder($beforeController));
            $response = $this->fireEvent(Nishchay::getEventCollection()
                            ->getEvents(Fire::BEFORE, $context, $scope, $order));
        }

        return $response;
    }

    /**
     * Fires event to be execute after called route.
     * 
     * @param   \Nishchay\Controller\Annotation\Controller         $controller
     * @param   \Nishchay\Controller\Annotation\Method\Method      $method
     * @return  mixed
     */
    public function fireAfterEvent(Controller $controller, ControllerMethod $method, $context, $scope)
    {
        $afterController = $controller->getAfterevent();
        $afterMethod = $method->getAfterevent();
        $response = $this->executeCallback($afterController, $afterMethod);
        if ($response === true) {
            $order = array_merge($this->getOrder($afterMethod), $this->getOrder($afterController));
            $response = $this->fireEvent(Nishchay::getEventCollection()
                            ->getEvents(Fire::AFTER, $context, $scope, $order));
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
     * @param instance $annotation
     * @param array $callback
     * @return boolean
     */
    private function fire($annotation, $callback)
    {
        if ($callback === false) {
            return true;
        }
        if (!($annotation->getOnce() && $annotation->isFired())) {
            $annotation->markFired();
            return $this->invokeCallback($callback, $annotation->getClass());
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
        foreach ($events as $annotation) {
            if ($annotation instanceof Method &&
                    $annotation->getFire()->getOnce() &&
                    $annotation->isFired()) {
                continue;
            }

            $response = $this->di
                    ->invoke($this->getInstance($annotation->getClass()), $annotation->getMethod());
            if ($response === null) {
                throw new NotSupportedException('Event must not return null.', null, null, 916008);
            }
            $annotation instanceof Method && $annotation->markFired();
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
        # We allow callback of class where annotation is defined or registered
        # event class.
        if ($callback[0] !== $class && !Nishchay::getEventCollection()->isExist($callback[0])) {
            throw new NotSupportedException('Invalid event callback [' .
                    implode('::', $callback) . '].'
                    . ' It should belongs to controller or any event class.', $class, null, 916009);
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
