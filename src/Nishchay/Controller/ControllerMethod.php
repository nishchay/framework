<?php

namespace Nishchay\Controller;

use Nishchay;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Utility\MethodInvokerTrait;
use Nishchay\Attributes\AttributeTrait;
use Nishchay\Attributes\Event\{
    AfterEvent,
    BeforeEvent
};
use Nishchay\Attributes\Controller\{
    OnlyGet,
    OnlyPost,
    RequiredGet,
    RequiredPost,
    ExceptionHandler
};
use Nishchay\Attributes\Controller\Method\{
    Route,
    Priority,
    Placeholder,
    NamedScope,
    Response,
    Service,
    NoService,
    Forwarder
};
use ReflectionAttribute;

/**
 * Controller method class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
class ControllerMethod
{

    use MethodInvokerTrait,
        AttributeTrait;

    /**
     * All annotation defined on method.
     * 
     * @var array 
     */
    private $annotation = [];

    /**
     * Route annotation object.
     * 
     * @var Route 
     */
    private $route;

    /**
     * Special annotation object.
     * 
     * @var Placeholder 
     */
    private $placeholder;

    /**
     * Priority of the defined route.
     * 
     * @var int 
     */
    private $priority;

    /**
     * Only get request parameter annotation object.
     * 
     * @var OnlyGet
     */
    private $onlyGet;

    /**
     * Only post request parameter annotation object.
     * 
     * @var OnlyPost 
     */
    private $onlyPost;

    /**
     * Required get request parameter annotation object.
     * 
     * @var RequiredGet 
     */
    private $requiredGet;

    /**
     * Required post request parameter annotation object.
     * 
     * @var RequiredPost 
     */
    private $requiredPost;

    /**
     * Response attribute.
     * 
     * @var Response 
     */
    private $response;

    /**
     * Session scope annotation.
     * 
     * @var NamedScope
     */
    private $namedscope;

    /**
     *
     * @var BeforeEvent
     */
    private $beforeEvent;

    /**
     *
     * @var fterEvent 
     */
    private $afterEvent;

    /**
     * Forwarder annotation object.
     * 
     * @var Forwarder
     */
    private $forwarder;

    /**
     * Exception handler annotation.
     * 
     * @var ExceptionHandler 
     */
    private $exceptionHandler;

    /**
     * Web service annotation.
     * 
     * @var Service
     */
    private $service = false;

    /**
     * Flag to make route not a service.
     * 
     * @var bool 
     */
    private $noservice = false;

    /**
     * Some annotation have default values.
     * This array helps setting default value when annotation not defined on method.
     * 
     * @var array 
     */
    private $defaultAttributes = [
        'forwarder' => [],
        'response' => ['type' => 'view'],
        'priority' => ['value' => 100]
    ];

    /**
     * Instance of controller class.
     * 
     * @var ControllerClass
     */
    private $controller;

    /**
     * Is route prepared from pattern.
     * 
     * @var bool
     */
    private $isPatterned = false;

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $attributes
     */
    public function __construct($class, $method, $attributes,
            ControllerClass $controller)
    {
        $this->setClass($class)
                ->setMethod($method);
        $this->controller = $controller;
        $this->buildRoute($attributes);
        $this->processAttributes($attributes);
        $this->process();
        $this->controller = null;
    }

    /**
     * Builds route based on  pattern if defined on controller.
     * 
     * @param type $attributes
     * @return type
     */
    public function buildRoute(&$attributes)
    {
        if (($routing = $this->controller->getRouting()) === null || $routing->getPattern() === null) {
            return;
        }

        $pattern = Nishchay::getRoutePatternCollection()->get($routing->getPattern());

        # This returns route instance or (route and placeholder) annotation in an array.
        $route = $pattern->processMethod($this->class, $this->method);

        if ($route === false) {
            unset($attributes['route']);
            return false;
        }

        foreach ($attributes as $key => $value) {
            $attributes[constant($value->getName() . '::NAME')] = $value;
            unset($attributes[$key]);
        }

        $attributes = $pattern->processConfig($attributes)->getAttributes();
        if ($route !== null) {
            $this->isPatterned = true;

            # If route has been defined on method then we will remove placeholder returned from route.
            if (isset($attributes['route']) && is_array($route) && $route['route']->getPath() !== null) {
                $route = $route['route'];
            }

            # This is to override parameter in pattern route by route attribute on contorller method.
            if (isset($attributes['route'])) {
                $patternRoute = is_array($route) ? $route['route'] : $route;
                $definedRoute = $attributes['route']->newInstance();

                $parameters = [
                    $definedRoute->getPath() === false ? $patternRoute->getPath() : $definedRoute->getPath(),
                    empty($definedRoute->getType()) ? $patternRoute->getType() : $definedRoute->getType(),
                    $definedRoute->getPrefix() === true ? $patternRoute->getPrefix() : $definedRoute->getPrefix(),
                    $definedRoute->getIncoming() === true ? $patternRoute->getIncoming() : $definedRoute->getIncoming(),
                    empty($definedRoute->getStage()) ? $patternRoute->getStage() : $definedRoute->getStage()
                ];
                if (is_array($route)) {
                    $route['route'] = new Route(...$parameters);
                } else {
                    $route = new Route(...$parameters);
                }
            }

            if (is_array($route)) {
                $attributes['route'] = $route['route']->setClass($this->class)
                        ->setMethod($this->method)
                        ->refactorPath($this->controller);
                $attributes['placeholder'] = $route['placeholder']->setClass($this->class)
                        ->setMethod($this->method)
                        ->setRoute($route['route']);
            } else {
                $attributes['route'] = $route;
            }
        }
    }

    /**
     * Applies default value to annotation and stores route to collection. 
     */
    public function process()
    {
        if ($this->getRoute() === null) {
            return;
        }

        $this->defaultAttributes['response']['type'] = Nishchay::getSetting('response.default');
        if (Nishchay::getSetting('service.enable') === true &&
                $this->getNoservice() === false) {
            $this->defaultAttributes['service'] = [];
        }

        foreach ($this->defaultAttributes as $attributeName => $defaultParameter) {
            if ($this->{$attributeName} === null) {
                $this->invokeMethod([$this, 'setDefault' . ucfirst($attributeName)],
                        [$defaultParameter]);
            }
        }

        if ($this->service !== false &&
                in_array(strtolower($this->response->getType()),
                        [Response::NULL_RESPONSE, Response::VIEW_RESPONSE])) {
            throw new InvalidAnnotationExecption('Route ['
                            . $this->getRoute()->getPath() . '] is service which requires'
                            . ' its response type either JSON or XML.',
                            $this->class, $this->method, 914013);
        }

        if (!empty($this->getRoute()->getPlaceholder())) {
            $placeholder = $this->getPlaceholder();
            if ($placeholder === null) {
                throw new InvalidAttributeException('[' . Placeholder::class .
                                '] required when there is placeholder segment'
                                . ' in route path.', $this->class, $this->method);
            }
            $placeholder->setRoute($this->getRoute())
                    ->verifyParameters();
        }


        Nishchay::getRouteCollection()->store($this->getPlaceholder(),
                $this->getRoute(), $this->getPriority()->getValue());
    }

    /**
     * Returns annotation defined on method.
     * 
     * @return array
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }

    /**
     * Returns route annotation object.
     * 
     * @return \Nishchay\Route\Annotation\Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Returns special annotation object.
     * 
     * @return Placeholder
     */
    public function getPlaceholder(): ?Placeholder
    {
        return $this->placeholder;
    }

    /**
     * Returns only get parameter list.
     * 
     * @return object
     */
    public function getOnlyGet()
    {
        return $this->onlyGet;
    }

    /**
     * Returns only post parameter list.
     * 
     * @return object
     */
    public function getOnlyPost()
    {
        return $this->onlyPost;
    }

    /**
     * Returns required get parameter list.
     * 
     * @return object
     */
    public function getRequiredGet()
    {
        return $this->requiredGet;
    }

    /**
     * Returns required post parameter list.
     * 
     * @return object
     */
    public function getRequiredPost()
    {
        return $this->requiredPost;
    }

    /**
     * 
     * @param type $sessionscope
     */
    public function getNamedscope()
    {
        return $this->namedscope;
    }

    /**
     * Sets route annotation information.
     * 
     * @param array $route
     */
    protected function setRoute(Route $route)
    {
        $route->setClass($this->class)
                ->setMethod($this->method)
                ->refactorPath($this->controller);

        $this->route = $route;
    }

    /**
     * Sets special value.
     * 
     * @param   array                           $placeholder
     * @throws  InvalidAnnotationExecption
     */
    protected function setPlaceholder(Placeholder $placeholder)
    {
        if (!$this->getRoute()) {
            throw new InvalidAttributeException('Attribute [' . $placeholder::class . ']'
                            . ' requires its dependent attribute [' . Route::class . '].',
                            $this->class, $this->method, 914015);
        }

        $this->placeholder = $placeholder
                ->setClass($this->class)
                ->setMethod($this->method);
    }

    /**
     * Sets only get parameter validation.
     * 
     * @param   array   $onlyGet
     */
    protected function setOnlyGet(OnlyGet $onlyGet)
    {
        $this->onlyGet = $onlyGet
                ->setClass($this->class)
                ->setMethod($this->method);
    }

    /**
     * Sets only post parameter validation.
     * 
     * @param   array   $onlyPost
     */
    protected function setOnlyPost(OnlyPost $onlyPost)
    {
        $this->onlyPost = $onlyPost
                ->setClass($this->class)
                ->setMethod($this->method);
    }

    /**
     * Sets required get parameter validation.
     * 
     * @param   array   $requiredGet
     */
    protected function setRequiredGet(RequiredGet $requiredGet)
    {
        $this->requiredGet = $requiredGet
                ->setClass($this->class)
                ->setMethod($this->method);
    }

    /**
     * Sets required post parameter validation.
     * 
     * @param   array   $requiredPost
     */
    protected function setRequiredPost(RequiredPost $requiredPost)
    {
        $this->requiredPost = $requiredPost
                ->setClass($this->class)
                ->setMethod($this->method);
    }

    /**
     * Returns response type of the method.
     * 
     * @return object
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets response type of the method defined in response annotation.
     * 
     * @param   array   $response
     */
    protected function setResponse(Response $response)
    {
        $this->response = $response
                ->setClass($this->class)
                ->setMethod($this->method);
        return $this;
    }

    /**
     * 
     * @param array $response
     * @return $this
     */
    protected function setDefaultResponse($response)
    {
        $this->setResponse(new Response(...$response));
        return $this;
    }

    /**
     * Sets session scope.
     * 
     * @param array $namedScope
     */
    protected function setNamedscope(NamedScope $namedScope)
    {
        $this->namedscope = $namedScope
                ->setClass($this->class)
                ->setMethod($this->method);
        return $this;
    }

    /**
     * Returns forward annotation object of the method.
     * 
     * @return object
     */
    public function getForwarder()
    {
        return $this->forwarder;
    }

    /**
     * Sets forwarder details defined in annotation.
     * 
     * @param   array   $forwarder
     */
    protected function setForwarder(Forwarder $forwarder)
    {
        $this->forwarder = $forwarder;
    }

    /**
     * 
     * @param array $forwarder
     * @return $this
     */
    protected function setDefaultForwarder($forwarder)
    {
        $this->setForwarder(new Forwarder(...$forwarder));
        return $this;
    }

    /**
     * Returns priority of the route.
     * 
     * @return  int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Sets priority of the route.
     * 
     * @param   int     $priority
     */
    protected function setPriority(Priority $priority)
    {
        $this->priority = $priority;
    }

    /**
     * 
     * @param int $priority
     * @return $this
     */
    protected function setDefaultPriority($priority)
    {
        $this->setPriority(new Priority(...$priority));
        return $this;
    }

    /**
     * Returns exception handler annotation.
     * 
     * @return ExceptionHandler
     */
    public function getExceptionhandler()
    {
        return $this->exceptionHandler;
    }

    /**
     * Sets exception handler annotation.
     * 
     * @param ExceptionHandler $exceptionhandler
     */
    public function setExceptionhandler(ExceptionHandler $exceptionhandler)
    {
        $this->exceptionHandler = $exceptionhandler
                ->setClass($this->class)
                ->setMethod($this->method);
    }

    /**
     * 
     * @return BeforeEvent
     */
    public function getBeforeEvent()
    {
        return $this->beforeEvent;
    }

    /**
     * 
     * @return AfterEvent
     */
    public function getAfterEvent()
    {
        return $this->afterEvent;
    }

    /**
     * Sets before event.
     * 
     * @param BeforeEvent $beforeEvent
     */
    protected function setBeforeEvent(BeforeEvent $beforeEvent)
    {
        $this->beforeEvent = $beforeEvent
                ->setClass($this->class)
                ->setMethod($this->method);
    }

    /**
     * Sets after event.
     * 
     * @param AfterEvent $afterEvent
     */
    protected function setAfterEvent(AfterEvent $afterEvent)
    {
        $this->afterEvent = $afterEvent
                ->setClass($this->class)
                ->setMethod($this->method);
    }

    /**
     * Returns web service annotation.
     * 
     * @return ervice|boolean
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Sets web service annotation.
     * 
     * @param array $service
     */
    public function setService(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Returns true route is not a service.
     * 
     * @return boolean
     */
    public function getNoservice()
    {
        return $this->noservice;
    }

    /**
     * Makes route not a service.
     * 
     * @param boolean $noservice
     */
    public function setNoservice(NoService $noservice)
    {
        $this->noservice = true;
        return $this;
    }

    /**
     * 
     * @param type $name
     * @param type $arguments
     * @throws ApplicationException
     */
    public function __call($name, $arguments)
    {
        throw new ApplicationException('Method [' . __CLASS__ . '::' . $name . '] does not exists.');
    }

}
