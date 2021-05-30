<?php

namespace Nishchay\Handler;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Processor\AbstractCollection;
use Nishchay\Handler\HandlerClass;
use Nishchay\Attributes\Handler\Handler;
use Nishchay\Processor\Names;
use Nishchay\Persistent\System;

/**
 * Exception detail class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Collection extends AbstractCollection
{

    /**
     * Exception handlers.
     * 
     * @var array 
     */
    private $collection = [
        Names::TYPE_SCOPE => [],
        Names::TYPE_CONTEXT => [],
        Names::TYPE_GLOBAL => []
    ];

    /**
     * Fetches handlers from persistent if application is live.
     */
    public function __construct()
    {
        if (Nishchay::isApplicationStageLive() && System::isPersisted('handlers')) {
            $this->collection = System::getPersistent('handlers');
        }
    }

    /**
     * Persists handlers
     */
    public function persist()
    {
        System::setPersistent('handlers', $this->collection);
    }

    /**
     * 
     * @param type $class
     * @param type $attributes
     */
    public function store($class, $attributes)
    {
        $this->checkStoring();
        $handler = (new HandlerClass($class, $attributes))->getHandler();

        switch ($handler->getType()) {
            case Names::TYPE_CONTEXT:
                $this->storeContextHandler($handler);
                break;
            case Names::TYPE_SCOPE:
                $this->storeScopeHandler($handler);
                break;
            case Names::TYPE_GLOBAL:
                $this->storeGlobalHandler($class);
                break;
            default:
                break;
        }
    }

    /**
     * 
     * @param type $handler
     * 
     * @throws ApplicationException
     */
    private function storeScopeHandler(Handler $handler)
    {
        if ($this->get(Names::TYPE_SCOPE, $handler->getName()) !== false) {
            throw new ApplicationException('Exception handler defined on [' . $handler->getClass() . '] class for scope ['
                            . $handler->getName() . '] already exist.',
                            $handler->getClass(), null, 919002);
        }
        $this->collection[Names::TYPE_SCOPE][$handler->getName()] = $handler->getClass();
    }

    /**
     * 
     * @param type $class
     * @param type $context
     * @throws ApplicationException
     */
    private function storeContextHandler(Handler $handler)
    {
        if ($this->get(Names::TYPE_CONTEXT, $handler->getName())) {
            throw new ApplicationException('Exception handler defined on [' . $handler->getClass() . '] class for context ['
                            . $handler->getName() . '] already exist.',
                            $handler->getClass(), null, 919003);
        }
        $this->collection[Names::TYPE_CONTEXT][$handler->getName()] = $handler->getClass();
    }

    /**
     * 
     * @param type $class
     * @throws ApplicationException
     */
    private function storeGlobalHandler($class)
    {
        if ($this->getGlobal() !== false) {
            throw new ApplicationException('Exception handler defined on [' .
                            $class . '] class for global already exist.',
                            $class, null, 919004);
        }
        $this->collection[Names::TYPE_GLOBAL] = $class;
    }

    /**
     * Returns handler if by name on given type.
     * 
     * @param   string          $type
     * @param   string          $name
     * @return  array|boolean
     */
    public function get($type, $name)
    {
        return isset($this->collection[$type][$name]) ? $this->collection[$type][$name] : false;
    }

    /**
     * Returns global handler if defined.
     * 
     * @return array
     */
    public function getGlobal()
    {
        return !empty($this->collection[Names::TYPE_GLOBAL]) ? $this->collection[Names::TYPE_GLOBAL] : false;
    }

    /**
     * Returns class name with it's context.
     * 
     * @param string $name
     * @return type
     */
    public function getContext($name)
    {

        foreach ([Names::TYPE_SCOPE, Names::TYPE_CONTEXT] as $type) {
            if (($detail = $this->getClassDetail($this->collection[$type], $name)) !== false) {
                return $detail;
            }
        }

        return isset($this->collection[Names::TYPE_GLOBAL][0]) ? $this->collection[Names::TYPE_GLOBAL][1] : false;
    }

    /**
     * Returns class details which matches with name.
     * 
     * @param   array           $array
     * @param   string          $name
     * @return  string|boolean
     */
    private function getClassDetail($array, $name)
    {
        foreach ($array as $element) {
            if (current($element) === $name) {
                return end($element);
            }
        }

        return false;
    }

    /**
     * Returns total number of defined exception handlers in an application.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->collection);
    }

}
