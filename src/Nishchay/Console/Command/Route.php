<?php

namespace Nishchay\Console\Command;

use Nishchay;
use Console_Table;
use Nishchay\Console\AbstractCommand;
use Nishchay\Console\Printer;
use Nishchay\Console\Help;
use Nishchay\Http\Request\Request;
use Nishchay\Attributes\Event\EventConfig;

/**
 * Route console command class.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Route extends AbstractCommand
{

    /**
     * Route path in case of first argument is not command.
     * 
     * @var string|boolean 
     */
    private $path = false;

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
     * Prints list of routes.
     * 
     * @return null
     */
    protected function printList()
    {
        return $this->printRouteList();
    }

    /**
     * Processes route command.
     * 
     * @return boolean
     */
    protected function processCommand()
    {
        # If first argument is not command it will considered as route path.
        $this->path = $this->arguments[0];
        if (count($this->arguments) > 1) {
            return $this->runRoutePathCommand();
        } else if (count($this->arguments) === 1) {
            $route = Nishchay::getRouteCollection()->getRoute($this->arguments[0], Request::GET, true);
            if ($route === false) {
                Printer::write('Route [' . $this->arguments[0] . '] does not exist.', Printer::RED_COLOR, 913015);
                return false;
            }
            $this->printRoutes($route);
        }
    }

    /**
     * Runs route related command.
     * 
     * @return type
     */
    private function runRoutePathCommand()
    {
        $command = strtolower($this->arguments[1]);
        if ($command === '-run') {
            return $this->path;
        } else if ($command === '-event') {
            return $this->printEvents($this->arguments[0]);
        }
        Printer::write('Invalid command: ' . $this->arguments[1], Printer::RED_COLOR, 913016);
    }

    /**
     * Prints all routes.
     * 
     * @return boolean
     */
    private function printRouteList()
    {
        return $this->printRoutes(Nishchay::getRouteCollection()->get());
    }

    /**
     * Prints passed routes.
     * 
     * @param array $routes
     * @return boolean
     */
    private function printRoutes($routes)
    {
        $table = new Console_Table();
        $table->setHeaders(['Path', 'Type', 'Class', 'Method']);
        foreach ($routes as $route) {
            $object = array_key_exists('object', $route) ?
                    $route['object'] : $route;
            $type = $object->getType() ? $object->getType() : ['*'];
            $table->addRow([wordwrap($object->getPath(), 40, PHP_EOL, true),
                implode('|', $type),
                $object->getClass(),
                $object->getMethod()
            ]);
        }
        Printer::write($table->getTable());
    }

    /**
     * Prints events of give route path.
     * 
     * @param string $path
     * @return boolean
     */
    private function printEvents($path)
    {
        $route = Nishchay::getRouteCollection()->getRoute($path, Request::GET);
        if ($route === false) {
            Printer::write('Route [' . $this->arguments[0] . '] does not exist.', Printer::RED_COLOR, 913017);
            return false;
        }

        $object = $route['object'];
        $controller = Nishchay::getControllerCollection()->getClass($object->getClass());
        $method = $controller->getMethod($object->getMethod());

        $context = $route['context'];
        $scope = '';
        if ($method->getNamedScope()) {
            $scope = $method->getNamedScope()->getName();
        }

        $allEvents = [
            EventConfig::BEFORE => $this->fetchEvents(EventConfig::BEFORE, $controller->getBeforeevent(), $method->getBeforeevent(), $context, $scope),
            EventConfig::AFTER => $this->fetchEvents(EventConfig::AFTER, $controller->getAfterevent(), $method->getAfterevent(), $context, $scope)
        ];

        return $this->printEventTable($allEvents);
    }

    /**
     * Fetch events for given context, scope, controller and method for $when
     * in order which it needs to be executed.
     * 
     * @param string $when
     * @param Object $controller
     * @param Object $method
     * @param string $context
     * @param string $scope
     * @return array
     */
    private function fetchEvents($when, $controller, $method, $context, $scope)
    {
        $allEvents = [];

        # Fetching event which are defined in event class for given context and
        # scope. This will also returns global events.
        $events = Nishchay::getEventCollection()
                ->getEvents($when, $context, $scope, $this->fetchEventOrder($controller, $method));

        # Event defined on controller class.
        if ($controller) {
            $allEvents[] = $controller->getCallback();
        }

        # Event defined on route method.
        if ($method) {
            $allEvents[] = $method->getCallback();
        }

        # Event which exist in event class and are to be called for given 
        # context, scope.
        foreach ($events as $event) {
            $allEvents[] = [
                $event->getClass(), $event->getMethod()
            ];
        }
        return $allEvents;
    }

    /**
     * Prints event table along events passed.
     * 
     * @param array $events
     * @return boolean
     */
    private function printEventTable($events)
    {
        $table = new Console_Table();
        $table->setHeaders(['When', 'Class', 'Method']);
        foreach ($events as $when => $whenEvents) {
            foreach ($whenEvents as $eventCallback) {
                $table->addRow([$when, $eventCallback[0], $eventCallback[1]]);
            }
        }
        Printer::write($table->getTable());
    }

    /**
     * Returns event order.
     * 
     * @param type $controller
     * @param type $method
     * @return type
     */
    private function fetchEventOrder($controller, $method)
    {
        $controllerEvent = $controller !== false ? $controller->getOrder() : [];
        $methodEvent = $method !== false ? $method->getOrder() : [];
        return array_merge($controllerEvent, $methodEvent);
    }

    /**
     * Prints help for route command.
     * 
     * @return boolean
     */
    public function getHelp()
    {
        new Help('route');
    }

    /**
     * Prints routes matched by path as defined in @Route definition.
     * 
     * @return boolean
     */
    public function getName()
    {
        if (empty($this->arguments[1])) {
            Printer::write('-name requires name to be passed.', Printer::RED_COLOR, 913018);
            return false;
        }

        $routes = Nishchay::getRouteCollection()->getByName($this->arguments[1]);
        if (empty($routes)) {
            Printer::write('No routes found for path: ' . $this->arguments[1], Printer::RED_COLOR, 913019);
            return false;
        }
        return $this->printRoutes($routes);
    }

    /**
     * Prints routes.
     * 
     * @return boolean
     */
    public function getMatch()
    {
        if (empty($this->arguments[1])) {
            Printer::write('-match requires regex pattern to be passed.', Printer::RED_COLOR, 913020);
            return false;
        }

        # Fetching routes by matching path as defined in Route attribute with
        # passed regex pattern.
        $routes = Nishchay::getRouteCollection()->getByName($this->arguments[1], true);
        if (empty($routes)) {
            Printer::write('No routes found for pattern: ' . $this->arguments[1], Printer::RED_COLOR, 913021);
            return false;
        }
        return $this->printRoutes($routes);
    }

    /**
     * Prints all routes belongs to controller class.
     * 
     * @return boolean
     */
    public function getController()
    {
        if (empty($this->arguments[1])) {
            Printer::write('-controller requires controller class name to be passed.', Printer::RED_COLOR, 913022);
            return false;
        }

        # Fetching controller class.
        if (($controller = Nishchay::getControllerCollection()->getClass($this->arguments[1])) === false) {
            Printer::write('Controller [' . $this->arguments[1] . '] does not exit.', Printer::RED_COLOR, 913023);
            return false;
        }

        $routes = [];
        foreach ($controller->getMethod() as $method) {
            $routes[] = $method->getRoute();
        }

        return $this->printRoutes($routes);
    }

    /**
     * Prints all routes belongs to context.
     * 
     * @return boolean
     */
    public function getContext()
    {
        if (empty($this->arguments[1])) {
            Printer::write('-context requires context name to be passed.', Printer::RED_COLOR, 913024);
            return false;
        }

        $routes = [];

        # Iterating over each routes to match its context with requested context.
        foreach (Nishchay::getControllerCollection()->get() as $controller) {
            if ($controller['context'] === $this->arguments[1]) {
                foreach ($controller['object']->getMethod() as $method) {
                    $routes[] = $method->getRoute();
                }
            }
        }

        # If there's no routes not found it means context is invalid.
        if (empty($routes)) {
            Printer::write('Invalid context: ' . $this->arguments[1], Printer::RED_COLOR, 913025);
            return false;
        }

        return $this->printRoutes($routes);
    }

}
