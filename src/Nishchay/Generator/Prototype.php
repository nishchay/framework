<?php

namespace Nishchay\Generator;

use Nishchay\Generator\Controller as ControllerGenerator;

/**
 * Prototype Generator class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
class Prototype extends AbstractGenerator
{

    /**
     * 
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name, 'prototype');
    }

    /**
     * Generates new prototype.
     * 
     * @return boolean
     */
    public function createNew()
    {
        $entityGenerator = new Entity($this->name);

        $entityClass = $entityGenerator->createNew();

        # Entity not created.
        if (is_string($entityClass) === false) {
            return false;
        }

        $classBaseName = $this->getClassBaseName($entityClass);

        # Creating form class.
        $formClass = $this->createForm($classBaseName);

        # Form class not created.
        if (is_string($formClass) === false) {
            return false;
        }

        # Creating controller
        return $this->createController($entityClass, $formClass, $classBaseName);
    }

    /**
     * Returns base class name.
     * 
     * @param string $class
     * @return type
     */
    private function getClassBaseName(string $class)
    {
        $expl = explode('\\', $class);

        return end($expl);
    }

    /**
     * Creates prototype from table.
     * 
     */
    public function createFromTable(?string $connection)
    {
        $entityGenerator = new Entity($this->name);

        $entityClass = $entityGenerator->createFromTable(null, $connection);

        # Entity not created.
        if (is_string($entityClass) === false) {
            return false;
        }

        $classBaseName = $this->getClassBaseName($entityClass);

        # Creating form class.
        $formClass = $this->createForm($classBaseName);

        # Form class not created.
        if (is_string($formClass) === false) {
            return false;
        }

        # Creating controller
        return $this->createController($entityClass, $formClass, $classBaseName);
    }

    /**
     * Creates form class.
     * 
     * @param type $classBaseName
     * @return type
     */
    private function createForm(string $classBaseName)
    {
        $formGenerator = new Form($classBaseName);

        return $formGenerator->createFromEntity(false);
    }

    /**
     * Creates controller class.
     * 
     * @param string $entityClass
     * @param string $formClass
     * @param string $classBaseName
     * @return type
     */
    private function createController(string $entityClass, string $formClass, string $classBaseName)
    {
        $controllerGenerator = new ControllerGenerator($classBaseName);

        return $controllerGenerator->createCrudPrototype($entityClass, $formClass, $classBaseName);
    }

    /**
     * Not required for this console command.
     * 
     * @return boolean
     */
    protected function isClassExists()
    {
        return false;
    }

    /**
     * Not required for this console command.
     * 
     * @return array
     */
    public function getMapper()
    {
        return [];
    }

}
