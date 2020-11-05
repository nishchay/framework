<?php

namespace Nishchay\Console\Command;

use Nishchay;
use Console_Table;
use Nishchay\Exception\ApplicationException;
use Nishchay\Console\AbstractCommand;
use Nishchay\Console\Help;
use Nishchay\Data\Reflection\DataClass;
use Nishchay\Console\Printer;
use Nishchay\Generator\Entity as EntityGenerator;

/**
 * Entity console command class.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Entity extends AbstractCommand
{

    /**
     * Initialization.
     * 
     * @param array $arguments
     */
    public function __construct($arguments)
    {
        parent::__construct($arguments);
    }

    /**
     * Prints all entities.
     */
    protected function printList()
    {
        $table = new Console_Table();
        $table->setHeaders(['Class', 'Table']);
        foreach (Nishchay::getEntityCollection()->get() as $class => $value) {
            $table->addRow([$class, $this->getDataClass($class)
                        ->getTableName()]);
        }
        Printer::write($table->getTable());
    }

    /**
     * Returns data class instance of $class.
     * 
     * @param string $name
     * @return \Nishchay\Data\Reflection\DataClass
     */
    private function getDataClass($name)
    {
        $class = Nishchay::getEntityCollection()->locate($name);

        if ($class === null) {
            throw new ApplicationException('Entity not found: ' . $name);
        }

        if ($class !== $name) {
            Printer::write('Located: ');
            Printer::yellow($class . PHP_EOL);
        }

        return new DataClass($class);
    }

    /**
     * Prints help for entity.
     * 
     * @return boolean
     */
    public function getHelp()
    {
        new Help('entity');
    }

    /**
     * Executes further command like -property, -derived, -identity, -trigger.
     * 
     * @return boolean
     */
    protected function processCommand()
    {
        if (count($this->arguments) === 1) {
            Printer::write('Invalid command: ' . implode(' ', $this->arguments), Printer::RED_COLOR, 913007);
            return false;
        }
        $commands = ['property', 'derived', 'identity', 'trigger'];

        $commnad = strtolower(substr($this->arguments[1], 1));
        if (in_array($commnad, $commands)) {
            $method = 'execute' . ucfirst($commnad) . 'Command';
            return $this->{$method}();
        }
    }

    /**
     * Prints all properties of entity.
     * 
     * @param boolean $derived
     * @param boolean $identity
     * @return boolean
     */
    protected function executePropertyCommand($derived = false, $identity = false)
    {
        $table = new Console_Table();
        $table->setHeaders(['Property', 'type']);
        $dataClass = $this->getDataClass($this->arguments[0]);
        $propertyCount = 0;
        foreach ($dataClass->getProperties() as $dataProperty) {

            # If requested to print only derived property we will skip
            # properties of self class.
            if ($derived && $dataProperty->isDerived() === false) {
                continue;
            }

            # If requested to print identity property we will skip other
            # properties.
            if ($identity && $dataProperty->isIdentity() === false) {
                continue;
            }
            $propertyCount++;
            $table->addRow([$dataProperty->getName(), $dataProperty->getDataType()]);
        }

        if ($propertyCount === 0) {
            $name = ($derived ? 'Derived' : ($identity ? 'Identity' : ''));
            Printer::write('[' . $name . '] property not found for in class: ' .
                    $this->arguments[0], Printer::RED_COLOR, 913008);
            return false;
        }

        Printer::write($table->getTable());
    }

    /**
     * Prints derived properties of entity.
     * 
     * @return boolean
     */
    protected function executeDerivedCommand()
    {
        return $this->executePropertyCommand(true);
    }

    /**
     * Prints identity property of entity
     * @return boolean
     */
    protected function executeIdentityCommand()
    {
        return $this->executePropertyCommand(false, true);
    }

    /**
     * Prints trigger defined for entity.
     * 
     * @return boolean
     */
    protected function executeTriggerCommand()
    {
        $triggers = $this->getDataClass($this->arguments[0])->getTriggers();
        if (empty($triggers['before']) && empty($triggers['after'])) {
            Printer::write('No trigger found for: ' .
                    $this->arguments[0], Printer::RED_COLOR, 913009);
            return false;
        }
        $table = new Console_Table();
        $table->setHeaders(['Callback method', 'For', 'When', 'Priority']);
        foreach ($triggers as $when => $whenTrigger) {
            foreach ($whenTrigger as $trigger) {
                $table->addRow([
                    implode('::', $trigger->getCallback()),
                    implode(',', $trigger->getFor()),
                    $when, $trigger->getPriority()
                ]);
            }
        }
        Printer::write($table->getTable());
    }

    /**
     * Creates entity based on command.
     * 
     * @return string
     */
    public function getCreate()
    {
        return $this->executeCreateCommand(EntityGenerator::class);
    }

    /**
     * Generates entity from DB, table or guide.
     * 
     * @return boolean
     */
    public function getGenerate()
    {

        if (isset($this->arguments[1]) === false) {
            Printer::write('-generate requires -db, -table or -new to be passed', Printer::RED_COLOR);
            return false;
        }

        list(, $type) = $this->arguments;
        if ($type === '-db') {
            (new EntityGenerator(null))->createFromDB($this->arguments[2] ?? null);
        } else if ($type === '-table') {
            if (isset($this->arguments[2]) === false) {
                Printer::write('-table requires table name to be passed', Printer::RED_COLOR);
                return false;
            }

            (new EntityGenerator($this->arguments[2]))->createFromTable(null, $this->arguments[3] ?? null);
        } else if ($type === '-new') {
            (new EntityGenerator(null))->createNew();
        } else {
            Printer::red('Invalid command: ' . $type);
        }

        return false;
    }

}
