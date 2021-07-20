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
use Nishchay\Attributes\Controller\Method\Forwarder as ForwarderAttribute;
use Nishchay\Attributes\Controller\Method\Route;
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
     * Controller attribute instance of the calling controller(class).
     * 
     * @var Nishchay\Controller\ControllerClass 
     */
    private $currentClass = null;

    /**
     * Currently processing route method.
     * 
     * @var \Nishchay\Controller\ControllerMethod
     */
    private $currentMethod = false;

    /**
     * Current stage number
     * 
     * @var int 
     */
    private static $stageNumber = 0;

    /**
     * Contains all stage information including all attribute,
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
     * Instance reflection current located controller.
     * 
     * @var ReflectionClass
     */
    private $reflection;

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
                
                # Persiting controller collections
                SystemPersistent::setPersistent('controllers',
                        Nishchay::getControllerCollection()->get());
                
                # Persiting entities collection
                SystemPersistent::setPersistent('entities',
                        Nishchay::getEntityCollection()->get());
                
                # Persiting configs
                SystemPersistent::setPersistent('cfigs', ['APP' => APP]);
                
                # Persisting views collection
                SystemPersistent::setPersistent('views', ViewCollection::get());
                
                # Persisting containers collection
                SystemPersistent::setPersistent('containers',
                        Nishchay::getContainerCollection()->getAll());
                
                # Persisting container facade names
                SystemPersistent::setPersistent('facades',
                        Nishchay::getContainerCollection()->getFacades());
            }
        } else {

            # This is because, container collection never gets called if only facade is used by application.
            # In this collection class, we have registered class auto load function. If container is not called
            # , then class auto load won't be registered
            Nishchay::getContainerCollection();

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
     * @param   ForwarderAttribute     $forwarder
     * @throws  NotSupportedException
     */
    protected function preCheck(ForwarderAttribute $forwarder)
    {

        $object = $this->getStageDetail('object');

        # Incomming request checking.
        if (self::$stageNumber === 1 && Nishchay::isApplicationRunningNoConsole() && $object->getIncoming() === false) {
            throw new RequestNotFoundException('Route [' .
                            $this->getStageDetail('urlString') . '] does not exist.',
                            null, null, 925036);
        }

        # Ascent forward checking.
        if (self::$stageNumber > 1 && $forwarder->getAscent() === false) {
            throw new NotSupportedException('Ascent forward not allowed'
                            . ' on route [' . $object->getPath() . '].',
                            $object->getClass(), $object->getMethod(), 925037);
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
            $this->getService()->check($this->getCurrentMethod());
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
                $this->getCurrentMethod() !== false && $this->getCurrentMethod()->getService() !== false;
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
        # In the case of abstract route, route method is not called.
        if ($this->instance !== null) {
            $response = $this->getDI()->invoke($this->instance,
                    $this->getCurrentMethod()->getMethod(), $this->parameter);
        } else {
            $viewName = $this->getCurrentMethod()->getResponse()->getView();
            if (empty($viewName) === false) {
                $response = $viewName;
            } else {
                $response = (empty(Nishchay::getSetting('response.abstractViewPath')) === false ?
                        Nishchay::getSetting('response.abstractViewPath') : '')
                        . '/' . $this->getCurrentMethod()->getRoute()->getPath();
            }

            $response = trim($response, '/');
        }

        return $this->respond($response);
    }

    /**
     * Respond with response.
     * 
     * @param mixed $response
     */
    private function respond($response)
    {
        if (is_object($response)) {
            # Before forwarding request to another route, we must fire 'after' event
            # for currently processed route.
            $this->eventManager->fireAfterEvent($this->getCurrentClass(),
                    $this->getCurrentMethod(), $this->getContext(),
                    $this->getScope());
            if ($response instanceof RequestForwarder) {
                $this->forwardRequest(new Forwarder($response),
                        $this->getCurrentMethod()->getClass(),
                        $this->getCurrentMethod()->getMethod());
            } else if ($response instanceof RequestRedirector) {
                Request::redirect($response->getRoute());
            }
        } else {
            $this->eventManager->fireAfterEvent($this->getCurrentClass(),
                    $this->getCurrentMethod(), $this->getContext(),
                    $this->getScope());
            new ResponseHandler($this->getCurrentMethod()->getClass(),
                    $this->getCurrentMethod()->getMethod(), $response);
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
        if ($this->getCurrentMethod()->getForwarder()->getDescent() === false) {
            throw new NotSupportedException('Descent forward not allowed on'
                            . ' route [' . $this->getCurrentMethod()->getRoute()->getPath() . '].',
                            $class, $method, 925039);
        }

        $this->setURL($response->getForwardedRoute(), self::$stageNumber + 1);
        $this->setStageDetail('mode', 'forwarded', self::$stageNumber + 1);
        $this->eventManager->fireAfterEvent($this->getCurrentClass(),
                $this->getCurrentMethod(), $this->getContext(),
                $this->getScope());
        $this->startStage();
    }

    /**
     * Returns instance of reflection class on current located controller.
     * 
     * @return ReflectionClass
     */
    private function getReflection()
    {
        if ($this->reflection !== null && $this->reflection->getName() === $this->getCurrentClass()->getClass()) {
            return $this->reflection;
        }

        return $this->reflection = new ReflectionClass($this->getCurrentClass()->getClass());
    }

    /**
     * Preparing controller properties and 
     * method parameter before it's method get called
     *  
     */
    private function prepare()
    {
        $method = $this->getCurrentMethod()->getMethod();
        $this->controller->processClassAttributes($this->getCurrentClass());
        $this->controller->processMethodAttributes($this->getCurrentMethod());

        $eventResponse = $this->eventManager
                ->fireBeforeEvent($this->getCurrentClass(),
                $this->getCurrentMethod(), $this->getContext(),
                $this->getScope());

        # Respond with bad request in case event respond with false.
        if ($eventResponse === false) {
            $url = $this->getStageDetail('urlString');
            throw new BadRequestException('Route [' .
                            (empty($url) ? Nishchay::getConfig('config.landingRoute') : $url)
                            . '] is not valid.',
                            $this->getCurrentMethod()->getClass(), $method,
                            925038);
        }

        # If event does not respond with true or null, it means that event
        # rsponded to generate response.
        if ($eventResponse !== true && $eventResponse !== null) {
            return $this->respond($eventResponse);
        }

        if ($this->getReflection()->isAbstract() === false) {
            $this->instance = $this->getDI()->create($this->getCurrentMethod()->getClass(),
                    [], true);
            new ControllerProperty($this->instance);

            # Setting up controller ennvironment.
            # We will first validates class attribute if any set.
            # Then we will process attribute defiend on route method.
            $this->controller->property($this->instance);

            #Preparing parameter to autobind values.
            $this->parameter = $this->controller
                    ->prepareMethodParameter(
                    new ReflectionMethod($this->instance, $method)
            );
        }
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
            throw new ApplicationException('Stage name [' . $name . '] is not valid.',
                            null, null, 925040);
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
            throw new ApplicationException('Can not restart application.', null,
                            null, 925041);
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
        if ($name === null) {
            return $stage;
        }

        if (!isset($stage[$name])) {
            throw new ApplicationException('Invalid state detail. Stage detail [' .
                            $name . '] does not exist.', null, null, 925042);
        }

        return $stage[$name];
    }

    /**
     * Returns current context.
     * 
     * @return string
     */
    public function getContext()
    {
        return $this->getStageDetail('context');
    }

    /**
     * Returns scope of current request.
     * 
     * @return mixed
     */
    public function getScope()
    {
        return $this->getStageDetail('scopeName');
    }

    /**
     * Returns All Stage detail.
     * 
     * @return array
     */
    private function getStage()
    {
        if (!isset(self::$stageDetail[self::$stageNumber])) {
            throw new ApplicationException('It seems stage is not started yet.',
                            null, null, 925043);
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

        # Route attribute instance.
        $route = $this->getStageDetail('object');

        $this->setCurrent($route);

        # Some pre verification is required to see incoming or ascent 
        # request is allowed or not.
        $this->preCheck($this->getCurrentMethod()->getForwarder());

        if ($this->getStageDetail('mode') !== Maintenance::MAINTENANCE &&
                Nishchay::getSetting(Maintenance::MAINTENANCE . '.active')) {
            $maintenance = new Maintenance();
            $maintenanceRoute = $maintenance->getRoute();

            # In maintenance mode
            if ($maintenanceRoute !== false) {
                $this->setURL($maintenanceRoute, self::$stageNumber + 1);
                $this->setStageDetail(Maintenance::MAINTENANCE, $maintenance,
                        self::$stageNumber + 1);
                $this->setStageDetail('mode', Maintenance::MAINTENANCE,
                        self::$stageNumber + 1);
                return $this->startStage();
            } else {
                $this->setStageDetail(Maintenance::MAINTENANCE, $maintenance,
                        self::$stageNumber);
            }
        }

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
     * @param Route $route
     */
    private function setCurrent(Route $route)
    {
        # Controller instance of currently located route's class.
        $this->currentClass = Nishchay::getControllerCollection()
                ->getClass($route->getClass());
        $this->currentMethod = $this->getCurrentClass()
                ->getMethod($route->getMethod());
        $this->setStageDetail('scopeName', $this->getScopeName());
    }

    /**
     * Returns scope name of current requested route.
     * 
     */
    private function getScopeName()
    {
        $scopeName = false;
        if (($scope = $this->getCurrentMethod()->getNamedscope()) !== null) {
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

        $matched = Nishchay::getRouteCollection()->getRoute($urlString,
                $requestMethod);

        if (!is_bool($matched)) {
            return $matched;
        }

        if ($matched === true) {
            throw new RequestMethodNotAllowedException('Request method'
                            . ' not supported for [' . $urlString . '].', null,
                            null, 925044);
        }

        throw new RequestNotFoundException('Route [' . $urlString . '] not found.',
                        null, null, 925045);
    }

    /**
     * Returns controller attribute instance of located route that is controller class.
     * 
     * @return \Nishchay\Controller\ControllerClass
     */
    public function getCurrentClass()
    {
        return $this->currentClass;
    }

    /**
     * 
     * @return \Nishchay\Controller\ControllerMethod
     */
    public function getCurrentMethod()
    {
        return $this->currentMethod;
    }

}
