<?php

namespace Nishchay\Processor;

use Exception;
use ReflectionMethod;
use ReflectionClass;
use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Controller\Controller;
use Nishchay\Controller\ControllerProperty;
use Nishchay\Controller\Forwarder;
use Nishchay\Event\EventManager;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\RequestNotFoundException;
use Nishchay\Exception\BadRequestException;
use Nishchay\Exception\RequestMethodNotAllowedException;
use Nishchay\Http\Request\Request;
use Nishchay\Http\Response\ResponseHandler;
use Nishchay\Http\View\Collection as ViewCollection;
use Nishchay\Maintenance\Maintenance;
use Nishchay\Persistent\System as SystemPersistent;
use Nishchay\Processor\SetUp\Organizer;
use Nishchay\Route\Annotation\Forwarder as ForwarderAnnotation;
use Nishchay\Route\Annotation\Route;
use Nishchay\Service\ServicePreProcess;
use Nishchay\Http\Request\RequestForwarder;
use Nishchay\Http\Request\RequestRedirector;
use Nishchay\DI\DI;
use Nishchay\Console\Console;

/**
 * Processing of the application
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Processor
{

    /**
     * Instance of located controller's for current stage.
     * 
     * @var object 
     */
    private $instance;

    /**
     * Controller processor instance.
     * This instance helps setting environment for the calling controller method.
     * 
     * @var Nishchay\Controller\Controller 
     */
    private $controller;

    /**
     * Event Manager instance.
     * 
     * @var Nishchay\Event\EventManager 
     */
    private $eventManager = null;

    /**
     * All assigned parameter of calling controller method
     * 
     * @var array 
     */
    private $parameter;

    /**
     * Controller annotation instance of the calling controller(class).
     * 
     * @var Nishchay\Controller\Annotation\Controller 
     */
    private $currentClass = null;

    /**
     * Currently processing route method.
     * 
     * @var Nishchay\Controller\Annotation\Method\Method 
     */
    private $currentMethod = false;

    /**
     * Current stage number
     * 
     * @var int 
     */
    private static $stageNumber = 0;

    /**
     * Contains all stage information including all annotation,
     * path,URL string and URL parts
     *  
     * @var array 
     */
    private static $stageDetail = [];

    /**
     * Web service process instance.
     * 
     * @var \Nishchay\Service\ServicePreProcess 
     */
    private $service;

    /**
     *
     * @var \Nishchay\Processor\InternalSession 
     */
    private $internalSession;

    /**
     *
     * @var \Nishchay\DI\DI
     */
    private $di;

    /**
     * Initialization
     */
    public function __construct()
    {
        $this->controller = new Controller();
        $this->eventManager = new EventManager();
    }

    /**
     * Initializing processor.
     */
    protected function init()
    {
        if (!SystemPersistent::isPersisted('controllers')) {
            new Organizer();
            if (Nishchay::isApplicationStageLive()) {
                SystemPersistent::setPersistent('controllers', Nishchay::getControllerCollection()->get());
                SystemPersistent::setPersistent('entities', Nishchay::getEntityCollection()->get());
                SystemPersistent::setPersistent('cfigs', ['APP' => APP]);
                SystemPersistent::setPersistent('views', ViewCollection::get());
            }
        } else {
            $constants = SystemPersistent::getPersistent('cfigs');
            foreach ($constants as $key => $value) {
                defined($key) || define($key, $value);
            }
        }
    }

    /**
     * Returns internal session instance.
     * 
     * @return \Nishchay\Processor\InternalSession
     */
    private function getInternalSession()
    {
        if ($this->internalSession !== null) {
            return $this->internalSession;
        }
        return $this->internalSession = new InternalSession();
    }

    /**
     * Sets value in internal session.
     * 
     * @param string $name
     * @param mixed $value
     */
    public function setInternalSessionValue($name, $value)
    {
        $this->getInternalSession()->{$name} = $value;
    }

    /**
     * Returns Internal session value.
     * 
     * @param string $name
     * @return string
     */
    public function getInternalSessionValue($name)
    {
        return isset($this->getInternalSession()->{$name}) ?
                $this->getInternalSession()->{$name} : false;
    }

    /**
     * Parses request.
     */
    private function parseRequest()
    {
        if (Request::server('URI') === false && Request::server('SCRIPT') === false) {
            return '';
        }

        $scriptName = Request::server('SCRIPT');
        $uri = parse_url(Request::server('URI'));
        $uri = isset($uri['path']) ? $uri['path'] : '';

        if (stripos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        } elseif (stripos($uri, dirname($scriptName)) === 0) {
            $uri = substr($uri, strlen(dirname($scriptName)));
        }

        $uri = trim($uri, '/');
        $this->setURL($uri, 1);
    }

    /**
     * Checks the rule to allow request or  not.
     * 
     * @param   ForwarderAnnotation     $forwarder
     * @throws  NotSupportedException
     */
    protected function preCheck(ForwarderAnnotation $forwarder)
    {

        $object = $this->getStageDetail('object');

        # Incomming request checking.
        if (self::$stageNumber === 1 && Nishchay::isApplicationRunningNoConsole() && $object->getIncoming() === false) {
            throw new RequestNotFoundException('Route [' .
                    $this->getStageDetail('urlString') . '] does not exist.', null, null, 925036);
        }

        # Ascent forward checking.
        if (self::$stageNumber > 1 && $forwarder->getAscent() === false) {
            throw new NotSupportedException('Ascent forward not allowed'
                    . ' on route [' . $object->getPath() . '].', $object->getClass(), $object->getMethod(), 925037);
        }
        $this->initServiceCheck();
    }

    /**
     * Pre checker for service.
     * 
     * @return null
     */
    private function initServiceCheck()
    {
        if ($this->isService()) {
            $this->getService()->check($this->currentMethod);
            return true;
        }

        return false;
    }

    /**
     * 
     * @return type
     */
    public function isService()
    {
        return Nishchay::getSetting('service.enable') === true ||
                $this->currentMethod !== false && $this->currentMethod->getService() !== false;
    }

    /**
     * Returns web service process instance.
     * 
     * @return \Nishchay\Service\ServicePreProcess
     */
    private function getService()
    {
        if ($this->service !== null) {
            return $this->service;
        }
        return $this->service = new ServicePreProcess();
    }

    /**
     * Calls locate controller method.
     */
    private function call()
    {
        $className = $this->currentMethod->getClass();
        $methodName = $this->currentMethod->getMethod();
        $context = $this->getStageDetail('context');
        $scope = $this->getStageDetail('scopeName');

        $eventResponse = $this->eventManager
                ->fireBeforeEvent($this->currentClass, $this->currentMethod, $context, $scope);
        if ($eventResponse === false) {
            $url = $this->getStageDetail('urlString');
            throw new BadRequestException('Route [' .
                    (empty($url) ? Nishchay::getConfig('config.landingRoute') : $url)
                    . '] is not valid.', $className, $methodName, 925038);
        }

        if ($eventResponse === true || $eventResponse === null) {
            $response = (new ReflectionMethod($className, $methodName))
                    ->invokeArgs($this->instance, $this->parameter);
        } else {
            $response = $eventResponse;
        }

        if (is_object($response)) {
            # Before forwarding request to another route, we must fire 'after' event
            # for currently processed route.
            $this->eventManager->fireAfterEvent($this->currentClass, $this->currentMethod, $context, $scope);
            if ($response instanceof RequestForwarder) {
                $this->forwardRequest(new Forwarder($response), $className, $methodName);
            } else if ($response instanceof RequestRedirector) {
                Request::redirect($response->getRoute());
            }
        } else {
            new ResponseHandler($className, $methodName, $response);
            $this->eventManager->fireAfterEvent($this->currentClass, $this->currentMethod, $context, $scope);
        }
    }

    /**
     * Forward request to another route.
     * 
     * @param   mixed                                           $response
     * @param   string                                          $class
     * @param   string                                          $method
     * @throws  \Nishchay\Exception\NotSupportedException
     */
    private function forwardRequest($response, $class, $method)
    {
        if ($this->currentMethod->getForwarder()->getDescent() === false) {
            throw new NotSupportedException('Descent forward not allowed on'
                    . ' route [' . $this->currentMethod->getRoute()->getPath() . '].', $class, $method, 925039);
        }

        $this->setURL($response->getForwardedRoute(), self::$stageNumber + 1);
        $this->setStageDetail('mode', 'forwarded', self::$stageNumber + 1);
        $this->eventManager->fireAfterEvent($this->currentClass, $this->currentMethod, $this->getStageDetail('context'), $this->getStageDetail('scopeName'));
        $this->startStage();
    }

    /**
     * Preparing controller properties and 
     * method parameter before it's method get called
     *  
     */
    private function prepare()
    {
        $method = $this->currentMethod->getMethod();

        # Setting up controller ennvironment.
        # We will first validates class annotation if any set.
        # Then we will process annotation defiend on route method.
        $this->controller->classAnnotation($this->currentClass);
        $this->controller->methodAnnotation($this->currentClass->getMethod($method));
        $this->controller->property($this->instance);

        #Preparing parameter to autobind values.
        $this->parameter = $this->controller
                ->prepareMethodParameter(
                new ReflectionMethod($this->instance, $method)
        );
        $this->call();
    }

    /**
     * Update stage info
     * 
     * @param   stirng      $name
     * @param   mixed       $value
     * @param   int         $stage
     */
    private function setStageDetail($name, $value, $stage = null)
    {
        if ($stage === null) {
            $stage = self::$stageNumber;
        }

        if (isset(self::$stageDetail[$stage][$name])) {
            throw new ApplicationException('Stage name [' . $name . '] is not valid.', null, null, 925040);
        }

        self::$stageDetail[$stage][$name] = $value;
    }

    /**
     * Sets url string and its part in stage detail.
     * 
     * @param srting $urlString
     * @param int $stageNumber
     */
    private function setURL($urlString, $stageNumber)
    {
        $this->setStageDetail('urlString', $urlString, $stageNumber);
        $this->setStageDetail('urlParts', explode('/', $urlString), $stageNumber);
    }

    /**
     * Starts first stage of request
     * 
     */
    public function start()
    {
        $this->init();
        if (self::$stageNumber > 0) {
            throw new ApplicationException('Can not restart application.', null, null, 925041);
        }

        if (Nishchay::isApplicationRunningNoConsole()) {
            $this->parseRequest();
        } else if ($this->setSupCLI() === false) {
            return false;
        }


        $this->setStageDetail('mode', 'incoming', self::$stageNumber + 1);
        if (Nishchay::getSetting('logger.enable')) {
            Nishchay::getLogger();
        }
        $this->startStage();
    }

    /**
     * Returns false if CLI does not pass --route.
     * 
     * @return boolean
     */
    private function setSupCLI()
    {
        if (Nishchay::isApplicationRunningForTests()) {
            return false;
        }

        $console = new Console(Request::server('argv'));
        $path = $console->run();
        if (empty($path)) {
            return false;
        }
        $urlString = $this->parseCommandLineURL($path);
        $this->setURL($urlString, 1);
        return true;
    }

    /**
     * Parse command line request string and sets query parameter to
     * Request class if any.
     * 
     * @param type $urlString
     */
    private function parseCommandLineURL($urlString)
    {
        $parse = parse_url($urlString);
        if (array_key_exists('query', $parse)) {
            parse_str($parse['query'], $query);

            # Setting query parameter to Request class's GET property.
            # We will need instance of Request class which we can get by 
            # using INSTANCE_PROPERTY constant.
            $reflection = new ReflectionClass(Request::class);
            $instance = $reflection->getProperty(Request::INSTANCE_PROPERTY);
            $instance->setAccessible(true);

            # Reflection of _GET property.
            $property = $reflection->getProperty('_GET');
            $property->setAccessible(true);
            $property->setValue($instance->getValue(), $query);
        }

        return $parse['path'];
    }

    /**
     * Returns Current stage number
     * 
     * @return array
     */
    public function stage()
    {
        return self::$stageNumber;
    }

    /**
     * Returns stage info of current stage
     * 
     * @param type $name
     * @return type
     */
    public function getStageDetail($name = null)
    {

        $stage = $this->getStage();
        if ($name == NULL) {
            return $stage;
        }

        if (!isset($stage[$name])) {
            throw new ApplicationException('Invalid state detail. Stage detail [' .
                    $name . '] does not exist.', null, null, 925042);
        }

        return $stage[$name];
    }

    /**
     * Returns All Stage detail.
     * 
     * @return array
     */
    private function getStage()
    {
        if (!isset(self::$stageDetail[self::$stageNumber])) {
            throw new ApplicationException('It seems stage is not started yet.', null, null, 925043);
        }
        return self::$stageDetail[self::$stageNumber];
    }

    /**
     * Starting stage with finding requesting route then saving detail. 
     * Next stage starts when request forwarded.
     */
    private function startStage()
    {
        self::$stageNumber++;
        $this->setStageDetail('stage', self::$stageNumber);

        # Locating current route. We will then add route information
        # to stage detail.
        foreach ($this->locateMatchingRoute() as $key => $value) {
            $this->setStageDetail($key, $value);
        }

        # Route annotation instance.
        $route = $this->getStageDetail('object');

        $this->setCurrent($route);

        # Some pre verification is required to see incoming or ascent 
        # request is allowed or not.
        $this->preCheck($this->currentMethod->getForwarder());

        if ($this->getStageDetail('mode') !== Maintenance::MAINTENANCE &&
                Nishchay::getSetting(Maintenance::MAINTENANCE . '.active')) {
            $maintenance = new Maintenance();
            $maintenanceRoute = $maintenance->getRoute();

            # In maintenance mode
            if ($maintenanceRoute !== false) {
                $this->setURL($maintenanceRoute, self::$stageNumber + 1);
                $this->setStageDetail(Maintenance::MAINTENANCE, $maintenance, self::$stageNumber + 1);
                $this->setStageDetail('mode', Maintenance::MAINTENANCE, self::$stageNumber + 1);
                return $this->startStage();
            } else {
                $this->setStageDetail(Maintenance::MAINTENANCE, $maintenance, self::$stageNumber);
            }
        }

        $this->instance = $this->getDI()->create($route->getClass(), [], true);
        new ControllerProperty($this->instance);
        $this->prepare();
    }

    /**
     * Returns instance of DI.
     * 
     * @return \Nishchay\DI\DI
     */
    private function getDI()
    {
        if ($this->di !== null) {
            return $this->di;
        }

        return $this->di = new DI();
    }

    /**
     * Sets current class, method and scope of the request.
     * 
     * @param \Nishchay\Route\Annotation\Route $route
     */
    private function setCurrent(Route $route)
    {
        # Controller instance of currently located route's class.
        $this->currentClass = Nishchay::getControllerCollection()
                ->getClass($route->getClass());
        $this->currentMethod = $this->currentClass
                ->getMethod($route->getMethod());
        $this->setStageDetail('scopeName', $this->getScopeName($route));
    }

    /**
     * Returns scope name of current requested route.
     * 
     */
    private function getScopeName()
    {
        $scopeName = false;
        if (($scope = $this->currentMethod->getNamedscope()) !== false) {
            $scopeName = $scope->getName();
        }
        return $scopeName;
    }

    /**
     * 
     * @return array
     */
    public function locateMatchingRoute()
    {
        # Current Stage URL string
        $urlString = $this->getStageDetail('urlString');

        # Empty means we need to call landing route of the application.
        if ($urlString === '') {
            $urlString = Nishchay::getLandingRoute();
        }

        # Current request method.
        $requestMethod = Request::server('METHOD');

        $matched = Nishchay::getRouteCollection()->getRoute($urlString, $requestMethod);

        if (!is_bool($matched)) {
            return $matched;
        }

        if ($matched === true) {
            throw new RequestMethodNotAllowedException('Request method'
                    . ' not supported for [' . $urlString . '].', null, null, 925044);
        }

        throw new RequestNotFoundException('Route [' . $urlString . '] not found.', null, null, 925045);
    }

    /**
     * Returns controller annotation instance of located route that is controller class.
     * 
     * @return string
     */
    public function getCurrentClass()
    {
        return $this->currentClass;
    }

}
