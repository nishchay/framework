<?php

namespace Nishchay\Console;

use Exception;

/**
 * Event console command class.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractCommand
{

    /**
     * Lists of arguments from console command.
     * 
     * @var array 
     */
    protected $arguments = [];

    /**
     * 
     * @param type $arguments
     */
    public function __construct($arguments)
    {
        array_shift($arguments);
        $this->arguments = $arguments;
    }

    /**
     * Returns true if command is valid.
     * 
     * @param type $command
     * @return type
     */
    protected function executeCommand($command)
    {
        if ($this->isValidCommand('-' . $command)) {
            return call_user_func_array([$this, $this->getMethodName($command)], [$this->arguments]);
        }

        throw new Exception('Invalid command [' . $command . '] in [' . implode(' ', $this->arguments) . ']', null, null, 913026);
    }

    /**
     * Returns TRUE if command starts with - and its method exist in 
     * respective command class.
     * 
     * @param type $command
     * @return boolean
     */
    protected function isValidCommand($command)
    {
        if (strpos($command, '-') === 0) {
            $command = substr($command, 1);
            return method_exists(static::class, $this->getMethodName($command));
        }

        return false;
    }

    /**
     * Returns method name for command.
     * 
     * @param string $command
     * @return string
     */
    private function getMethodName($command)
    {
        return 'get' . ucfirst(strtolower($command));
    }

    /**
     * 
     * @return type
     */
    public function run()
    {
        if (empty($this->arguments)) {
            return $this->printList();
        }
        if ($this->isValidCommand($this->arguments[0])) {
            return $this->executeCommand(substr($this->arguments[0], 1));
        } else {
            return $this->processCommand();
        }
    }

    /**
     * Print list.
     */
//    abstract protected function printList();

    /**
     * Throws invalid command error.
     * 
     * @return boolean
     */
    protected function processCommand()
    {
        Printer::write('Invalid command: ' . $this->arguments[0], Printer::RED_COLOR, 913027);
    }

    /**
     * Creates classes based on command.
     * 
     * @param string $generatorClass
     * @return boolean
     */
    protected function executeCreateCommand($generatorClass)
    {
        if (empty($this->arguments[1])) {
            Printer::write('-create requires new name to be passed', Printer::RED_COLOR, 913028);
            return false;
        }

        try {
            $filePath = $hint = false;
            $generator = new $generatorClass($this->arguments[1]);
            if (count($this->arguments) > 2) {
                if ($this->arguments[2] === '-crud') {
                    $filePath = $generator->createCrud();
                } else if ($this->arguments[2] === '-template') {
                    if (empty($this->arguments[3])) {
                        Printer::write('-template requires template name'
                                . ' to be passed', Printer::RED_COLOR, 913029);
                        return false;
                    }
                    $response = $generator->createFromTemplate($this->arguments[3]);
                    if ($response !== false) {
                        $filePath = $response['path'];
                        if (!empty($response['hint'])) {
                            $hint = $response['hint'];
                        }
                    }
                } else {
                    Printer::write('Invalid command: ' .
                            implode(' ', $this->arguments), Printer::RED_COLOR, 913030);
                    return false;
                }
            } else {
                $filePath = $generator->createEmpty();
            }

            if ($filePath !== false) {
                Printer::write('Created at ');
                Printer::write($filePath, Printer::YELLOW_COLOR);
                if ($hint !== false) {
                    Printer::write(PHP_EOL . $hint, Printer::GREY_COLOR);
                }
            }
        } catch (Exception $e) {
            Printer::write($e->getMessage(), Printer::RED_COLOR, null, null, 913031);
            return false;
        }
    }

    /**
     * Prints list of routes.
     * 
     * @return null
     */
    abstract protected function printList();

}
