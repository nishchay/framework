<?php

namespace Nishchay\Console\Command;

use Nishchay;
use Nishchay\Console\AbstractCommand;
use Console_Table;
use Nishchay\Console\Printer;
use Nishchay\Processor\Names;
use Nishchay\Event\Annotation\Method\Fire;
use Nishchay\Console\Help;

/**
 * Event console command class.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Event extends AbstractCommand
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
     * Prints event list.
     * 
     * @return boolean
     */
    protected function printList()
    {
        return $this->printEvents(Nishchay::getEventCollection()->get());
    }

    /**
     * Prints events.
     * 
     * @param array $events
     * @return null
     */
    public function printEvents($events)
    {
        $table = new Console_Table();
        $table->setHeaders(['Type', 'Type For', 'When', 'Class', 'Method']);
        $count = 0;
        foreach ($events as $eventType => $event) {
            foreach ($event as $when => $whenEvent) {
                if ($eventType === Names::TYPE_GLOBAL) {
                    foreach ($whenEvent as $eventClass) {
                        $count++;
                        $table->addRow([$eventType, '-', $when, $eventClass->getClass(), $eventClass->getMethod()]);
                    }
                    continue;
                }


                foreach ($whenEvent as $forType => $forTypeEvent) {
                    foreach ($forTypeEvent as $eventClass) {
                        $count++;
                        $table->addRow([$eventType, $forType, $when, $eventClass->getClass(), $eventClass->getMethod()]);
                    }
                }
            }
        }

        if ($count === 0) {
            Printer::red('No events found.');
            return false;
        }
        Printer::write($table->getTable());
    }

    /**
     * Prints events belongs to context.
     * 
     * @return string
     */
    protected function getContext()
    {
        if (empty($this->arguments[1])) {
            Printer::write('-context requires context' .
                    ' name to be passed.', Printer::RED_COLOR, 913010);
            return false;
        }

        $name = $this->arguments[1];
        $eventCollection = Nishchay::getEventCollection();
        $beforeEvent = $eventCollection->getContextEvent($name, Fire::BEFORE);
        $afterEvent = $eventCollection->getContextEvent($name, Fire::AFTER);
        if (empty($beforeEvent) && empty($afterEvent)) {
            Printer::write('No event exist for context: ' . $name, Printer::RED_COLOR, 913011);
            return false;
        }
        return $this->printTypeEvent(Names::TYPE_CONTEXT, $name, $beforeEvent, $afterEvent);
    }

    /**
     * Prints events.
     * 
     * @param string $type
     * @param string $name
     * @param string $before
     * @param string $after
     * @return null
     */
    public function printTypeEvent($type, $name, $before, $after)
    {
        return $this->printEvents([
                    $type => [
                        Fire::BEFORE => [
                            $name => $before
                        ],
                        Fire::AFTER => [
                            $name => $after
                        ]
                    ]
        ]);
    }

    /**
     * Prints events belongs to scope.
     * 
     * @return boolean
     */
    protected function getScope()
    {
        if (empty($this->arguments[1])) {
            Printer::write('-scope requires scope' .
                    ' name to be passed', Printer::RED_COLOR, 913012);
            return false;
        }

        $name = $this->arguments[1];
        $eventCollection = Nishchay::getEventCollection();
        $beforeEvent = $eventCollection->getScopeEvent($name, Fire::BEFORE);
        $afterEvent = $eventCollection->getScopeEvent($name, Fire::AFTER);
        if (empty($beforeEvent) && empty($afterEvent)) {
            Printer::write('No event exist for scope: ' . $name, Printer::RED_COLOR, 913013);
            return false;
        }
        return $this->printTypeEvent(Names::TYPE_SCOPE, $name, $beforeEvent, $afterEvent);
    }

    /**
     * Prints events belongs to global.
     * 
     * @return boolean
     */
    protected function getGlobal()
    {
        $eventCollection = Nishchay::getEventCollection();
        $beforeEvent = $eventCollection->getGlobalEvent(Fire::BEFORE);
        $afterEvent = $eventCollection->getGlobalEvent(Fire::AFTER);
        if (empty($beforeEvent) && empty($afterEvent)) {
            Printer::write('No global event found.', Printer::RED_COLOR, 913014);
            return false;
        }
        $events = [
            Names::TYPE_GLOBAL => [
                Fire::BEFORE => $beforeEvent,
                Fire::AFTER => $afterEvent
            ]
        ];
        return $this->printEvents($events);
    }

    /**
     * Prints help for route command.
     * 
     * @return boolean
     */
    public function getHelp()
    {
        new Help('event');
    }

}
