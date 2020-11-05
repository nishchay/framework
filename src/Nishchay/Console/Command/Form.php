<?php

namespace Nishchay\Console\Command;

use Nishchay\Console\AbstractCommand;
use Nishchay\Console\Printer;
use Nishchay\Generator\Form as FormGenerator;
use Nishchay\Console\Help;

/**
 * Form console command class.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Form extends AbstractCommand
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
     * Generates entity from DB, table or guide.
     * 
     * @return boolean
     */
    public function getGenerate()
    {

        if (isset($this->arguments[1]) === false) {
            Printer::write('-generate requires -entity or -new to be passed', Printer::RED_COLOR);
            return false;
        }

        list(, $type) = $this->arguments;
        if ($type === '-entity') {
            if (isset($this->arguments[2]) === false) {
                Printer::write('-entity requires entity name to be passed', Printer::RED_COLOR);
                return false;
            }

            (new FormGenerator($this->arguments[2]))->createFromEntity();
        } else {
            Printer::red('Invalid command: ' . $type);
        }

        return false;
    }

    protected function printList(): void
    {
        Printer::red('Invalid command: Pass -generate to generate based on table or interactive command.');
    }

    /**
     * Prints help for entity.
     * 
     * @return boolean
     */
    public function getHelp()
    {
        new Help('form');
    }

}
