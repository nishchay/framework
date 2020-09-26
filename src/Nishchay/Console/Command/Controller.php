<?php

namespace Nishchay\Console\Command;

use Nishchay;
use AnnotationParser;
use ReflectionClass;
use ReflectionMethod;
use Console_Table;
use Nishchay\Console\Printer;
use Nishchay\Console\AbstractCommand;
use Nishchay\Console\Help;
use Nishchay\Generator\Controller as ControllerGenerator;

/**
 * Controller console command class.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Controller extends AbstractCommand
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
     * Prints list of controllers.
     * 
     * @return null
     */
    protected function printList()
    {
        return $this->printControllerList();
    }

    /**
     * Processes command.
     * 
     * @return null
     */
    protected function processCommand()
    {
        $class = $this->arguments[0];
        if (count($this->arguments) === 1) {

            if (($class = $this->locateClass($class)) === false) {
                return false;
            }

            return $this->printControllerList($class);
        }

        if (!$this->isValidCommand($this->arguments[1])) {
            Printer::write('Invalid command: ' . $this->arguments[1], Printer::RED_COLOR, 913001);
            return false;
        }

        $this->executeCommand(substr($this->arguments[1], 1));
    }

    /**
     * Prints controller list.
     * 
     * @return null
     */
    protected function printControllerList($specific = false)
    {
        return $this->printControllers(Nishchay::getControllerCollection()->get(), $specific);
    }

    /**
     * Prints controller list table.
     * 
     * @param type $controllers
     */
    private function printControllers($controllers, $specific = false)
    {
        $table = new Console_Table();
        $table->setHeaders(['Class', 'Context', 'Routes']);

        $match = false;
        foreach ($controllers as $row) {
            if ($specific !== false && $specific !== $row['object']->getClass()) {
                continue;
            }
            $match = true;
            $table->addRow([$row['object']->getClass(), $row['context'], count($row['object']->getMethod())]);
        }

        if ($specific !== false && $match === false) {
            Printer::write('No controller found with name: ' . $specific, Printer::RED_COLOR, 913002);
            return false;
        }

        Printer::write($table->getTable());
    }

    /**
     * Prints help for controller command.
     * 
     * @return boolean
     */
    public function getHelp()
    {
        if (count($this->arguments) > 1) {
            Printer::write('Invalid command: controller ' .
                    implode(' ', $this->arguments), Printer::RED_COLOR, 913003);
            return false;
        }
        new Help('controller');
    }

    /**
     * Locates controller class from name.
     * 
     * @param type $name
     * @return boolean
     */
    public function locateClass($name)
    {
        $class = Nishchay::getControllerCollection()
                ->locate($name);

        if ($class === null) {
            Printer::red('No controller found with name: ' .
                    $name, 911302);
            return false;
        }

        if ($class !== $name) {
            Printer::write('Located: ');
            Printer::yellow($class . PHP_EOL);
        }

        return $class;
    }

    /**
     * Prints annotation defined on controller class.
     * It prints annotation defined on method if -method command passed.
     * 
     * @return boolean
     */
    public function getAnnotation()
    {
        if (($class = $this->locateClass($this->arguments[0])) === false) {
            return false;
        }

        $controller = Nishchay::getControllerCollection()
                ->getClass($class);

        # If only argument is passed we will print annotation defined on
        # controller class.
        if (count($this->arguments) === 2) {
            $reflection = new ReflectionClass($controller->getClass());
        } else {
            # Verifying if further command is -method or not.
            if ($this->arguments[2] !== '-method' || !isset($this->arguments[3])) {
                Printer::write('Invalid command: ' . $this->arguments[2], Printer::RED_COLOR, 913005);
                return false;
            }

            # Checking if method exist in controller class or not.
            if ($controller->getMethod($this->arguments[3]) === false) {
                Printer::write('Method [' . $class .
                        '::' . $this->arguments[3] . '] does not exist', Printer::RED_COLOR, 913006);
                return false;
            }
            $reflection = new ReflectionMethod($class, $this->arguments[3]);
        }

        # Parsing annotation defined on controller or method.
        $annotations = AnnotationParser::getAnnotationAsString($reflection->getDocComment());

        # Priting annotation.F
        $table = new Console_Table();
        $table->setHeaders(['Name', 'Parameter']);

        foreach ($annotations as $name => $parameter) {
            $table->addRow([$name, substr($parameter, 1, -1)]);
        }

        Printer::write($table->getTable());
    }

    /**
     * Executes create command.
     * 
     * @return boolean
     */
    public function getCreate()
    {
        return $this->executeCreateCommand(ControllerGenerator::class);
    }

}
