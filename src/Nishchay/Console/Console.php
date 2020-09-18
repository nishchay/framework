<?php

namespace Nishchay\Console;

use Nishchay\Processor\Application;
use Nishchay\Console\Command\Route;
use Nishchay\Console\Command\Event;
use Nishchay\Console\Command\Entity;
use Nishchay\Console\Printer;
use Nishchay\Console\Command\Controller;
use Nishchay\Console\Command\Install;

/**
 * Description of Console
 *
 * @author bpatel
 */
class Console extends AbstractCommand
{

    protected $validCommands = [
        'route',
        'controller',
        'entity',
        'handler',
        'event'
    ];

    /**
     * Alias for command.
     * 
     * @var type 
     */
    private $alias = [
        'v' => 'version',
        '-v' => 'version',
        '-version' => 'version',
        'r' => 'route',
        'c' => 'controller',
        'e' => 'entity',
        'h' => 'handler',
        'ev' => 'event',
    ];

    public function __construct($arguments)
    {
        parent::__construct($arguments);

        if (isset($this->arguments[0])) {
            if (array_key_exists($this->arguments[0], $this->alias)) {
                $this->arguments[0] = $this->alias[$this->arguments[0]];
            }

            $this->arguments[0] = '-' . $this->arguments[0];
        }
    }

    /**
     * Executes route command.
     * 
     * @param array $arguments
     * @return boolean
     */
    protected function getRoute($arguments)
    {
        $route = new Route($arguments);

        if (($path = $route->run()) !== false) {
            return $path;
        }

        return false;
    }

    /**
     * Executes event command.
     * 
     * @param array $arguments
     * @return boolean
     */
    protected function getEvent($arguments)
    {
        $event = new Event($arguments);
        $event->run();
        return false;
    }

    /**
     * Prints framework version information.
     * 
     * @return boolean
     */
    public function getVersion()
    {
        Printer::write(Application::NAME . PHP_EOL . 'Version number: ');
        Printer::write('v' . Application::VERSION . ' ', Printer::GREEN_COLOR);
        Printer::write(PHP_EOL . 'Version name: ');
        Printer::write(Application::VERSION_NAME, Printer::YELLOW_COLOR);
    }

    /**
     * Executes help command.
     * 
     * @return boolean
     */
    public function getHelp()
    {
        return $this->printHelp();
    }

    /**
     * Executes controller command.
     * 
     * @param array $arguments
     * @return boolean
     */
    public function getController($arguments)
    {
        $controller = new Controller($arguments);

        return $controller->run();
    }

    /**
     * Executes entity command.
     * 
     * @param array $arguments
     * @return boolean
     */
    public function getEntity($arguments)
    {
        $entity = new Entity($arguments);
        return $entity->run();
    }

    /**
     * Executes install command
     * 
     * @return boolean
     */
    public function getInstall()
    {
        $install = new Install();
        return $install->run();
    }

    /**
     * Prints help.
     * 
     * @return type
     */
    protected function printList()
    {
        (new Help());
        return false;
    }

}
