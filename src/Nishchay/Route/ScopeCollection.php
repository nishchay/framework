<?php

namespace Nishchay\Route;

use Nishchay;
use Nishchay\Processor\AbstractCollection;
use Nishchay\Persistent\System;

/**
 * Scope collection.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ScopeCollection extends AbstractCollection
{

    /**
     * Collection.
     * 
     * @var array 
     */
    private $collection = [];

    /**
     * Initialization
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Saves instance to route persistent file if application in live stage
     * and updates flag to store mode.
     * 
     * @return null
     */
    private function init(): void
    {
        if (Nishchay::isApplicationStageLive() && System::isPersisted('scope')) {
            $this->collection = System::getPersistent('scope');
        }
    }

    /**
     * Persists scopes
     */
    public function persist(): void
    {
        System::setPersistent('scope', $this->collection);
    }

    /**
     * Stores scope name to collection.
     * 
     * @param string $name
     */
    public function store(string $name): void
    {
        $this->checkStoring();
        $name = strtolower($name);
        $this->collection[$name] = $name;
    }

    /**
     * Returns all scopes.
     * 
     * @return string
     */
    public function get(): array
    {
        return $this->collection;
    }

    /**
     * Returns true if scope exists.
     * 
     * @param string $name
     * @return bool
     */
    public function isExists(string $name): bool
    {
        return array_key_exists(strtolower($name), $this->collection);
    }

    /**
     * Returns total count of scope.
     * 
     * @return int
     */
    public function count(): int
    {
        return count($this->collection);
    }

}
