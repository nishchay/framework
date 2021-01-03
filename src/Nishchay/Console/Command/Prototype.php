<?php

namespace Nishchay\Console\Command;

use Nishchay\Console\AbstractCommand;
use Nishchay\Console\Printer;
use Nishchay\Generator\Prototype as PrototypeGenerator;
use Nishchay\Console\Help;

/**
 * Prototype console command class.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Prototype extends AbstractCommand
{

    public function __construct($arguments)
    {
        parent::__construct($arguments);
    }

    public function getGenerate()
    {

        if (isset($this->arguments[1]) === false) {
            Printer::red('-generate requires -entity or -new to be passed', 913039);
            return false;
        }

        list(, $type) = $this->arguments;
        if ($type === '-table') {
            if (isset($this->arguments[2]) === false) {
                Printer::red('-table requires entity name to be passed', 913040);
                return false;
            }

            (new PrototypeGenerator($this->arguments[2]))->createFromTable($this->arguments[3] ?? null);
        } else if ($type === '-new') {

            (new PrototypeGenerator(null))->createNew();
        } else {
            Printer::red('Invalid command: ' . $type, 913041);
        }
    }

    /**
     * 
     * @return boolean
     */
    protected function printList(): void
    {
        Printer::red('Invalid command: Pass -generate to generate based on table or interactive command.', 913042);
    }

    /**
     * Prints help for entity.
     * 
     * @return boolean
     */
    public function getHelp()
    {
        new Help('prototype');
    }

}
