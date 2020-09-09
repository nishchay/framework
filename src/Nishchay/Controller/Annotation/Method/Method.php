<?php

namespace Nishchay\Controller\Annotation\Method;

use Nishchay;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Controller\Annotation\Method\Doc;
use Nishchay\Controller\Annotation\Method\Response;
use Nishchay\Controller\Annotation\Method\Priority;
use Nishchay\Controller\Annotation\RequiredGet;
use Nishchay\Controller\Annotation\RequiredPost;
use Nishchay\Controller\Annotation\OnlyGet;
use Nishchay\Controller\Annotation\OnlyPost;
use Nishchay\Route\Annotation\Forwarder;
use Nishchay\Route\Annotation\Route;
use Nishchay\Route\Annotation\Placeholder;
use Nishchay\Route\Annotation\NamedScope;
use Nishchay\Controller\Annotation\ExceptionHandler;
use Nishchay\Event\Annotation\AfterEvent;
use Nishchay\Event\Annotation\BeforeEvent;
use Nishchay\Service\Annotation\Service;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * Description of MethodAnnotation
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Method extends BaseAnnotationDefinition
{

    use MethodInvokerTrait;

    /**
     * All annotation defined on method.
     * 
     * @var array 
     */
    private $annotation = [];

    /**
     * Route annotation object.
     * 
     * @var object 
     */
    private $route = false;

    /**
     * Special annotation object.
     * 
     * @var object 
     */
    private $placeholder = false;

    /**
     * Priority of the defined route.
     * 
     * @var int 
     */
    private $priority = false;

    /**
     * Doc annotation object.
     * 
     * @var \Nishchay\Controller\Annotation\Method\Doc 
     */
    private $doc = false;

    /**
     * Only get request parameter annotation object.
     * 
     * @var \Nishchay\Controller\Annotation\OnlyGet 
     */
    private $onlyGet = false;

    /**
     * Only post request parameter annotation object.
     * 
     * @var \Nishchay\Controller\Annotation\OnlyPost 
     */
    private $onlyPost = false;

    /**
     * Required get request parameter annotation object.
     * 
     * @var \Nishchay\Controller\Annotation\RequiredGet 
     */
    private $requiredGet = false;

    /**
     * Required post request parameter annotation object.
     * 
     * @var \Nishchay\Controller\Annotation\RequiredPost 
     */
    private $requiredPost = false;

    /**
     * Response annotation object.
     * 
     * @var \Nishchay\Controller\Annotation\Method\Response 
     */
    private $response = false;

    /**
     * Session scope annotation.
     * 
     * @var \Nishchay\Route\Annotation\NamedScope
     */
    private $namedscope = false;

    /**
     *
     * @var \Nishchay\Event\Annotation\BeforeEvent
     */
    private $beforeEvent = false;

    /**
     *
     * @var \Nishchay\Event\Annotation\AfterEvent 
     */
    private $afterEvent = false;

    /**
     * Forwarder annotation object.
     * 
     * @var \Nishchay\Route\Annotation\Forwarder 
     */
    private $forwarder = false;

    /**
     * Exception handler annotation.
     * 
     * @var \Nishchay\Controller\Annotation\ExceptionHandler 
     */
    private $exceptionHandler = false;

    /**
     * Web service annotation.
     * 
     * @var \Nishchay\Service\Annotation\Service 
     */
    private $service = false;

    /**
     * Flag to make route not a service.
     * @var type 
     */
    private $noservice = false;

    /**
     * Some annotation have default values.
     * This array helps setting default value when annotation not defined on method.
     * 
     * @var array 
     */
    private $defaultAnnotation = [
        'forwarder' => [],
        'response' => ['type' => 'view'],
        'priority' => ['value' => 100]
    ];

    /**
     *
     * @var type 
     */
    private $controller;

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $annotation
     */
    public function __construct($class, $method, $annotation, $controller)
    {
        parent::__construct($class, $method);
        $this->annotation = ArrayUtility::customeKeySort($annotation, ['route', 'special', 'service']);
        $this->controller = $controller;
        $this->selfSetter();
        $this->process();
        $this->controller = NULL;
    }

    /**
     * Applies default value to annotation and stores route to collection. 
     */
    public function process()
    {
        if ($this->route === false) {
            return;
        }

        $this->defaultAnnotation['response']['type'] = Nishchay::getSetting('response.default');
        if (Nishchay::getSetting('service.enable') === true &&
                $this->getNoservice() === false) {
            $this->defaultAnnotation['service'] = [];
        }

        foreach ($this->defaultAnnotation as $annotation => $defaultParameter) {
            if ($this->{$annotation} === false) {
                $this->invokeMethod([$this, 'set' . ucfirst($annotation)], [$defaultParameter]);
            }
        }

        if ($this->service !== false &&
                in_array(strtolower($this->response->getType()), [Response::NULL_RESPONSE, Response::VIEW_RESPONSE])) {
            throw new InvalidAnnotationExecption('Route ['
                    . $this->getRoute()->getPath() . '] is service which requires'
                    . ' its response type either JSON or XML.', $this->class, $this->method, 914013);
        }
        Nishchay::getRouteCollection()->store($this->placeholder, $this->route, $this->priority->getValue());
    }

    /**
     * 
     * @throws InvalidAnnotationExecption
     */
    private function selfSetter()
    {
        foreach ($this->annotation as $key => $value) {
            if (preg_match("#doc_(\w+)#", $key, $param)) {
                $this->setDoc($param[1]);
                continue;
            }

            $method = 'set' . ucfirst($key);
            if (!method_exists($this, $method)) {
                throw new InvalidAnnotationExecption('Invalid annotation ['
                        . ' ' . $key . '].', $this->class, $this->method, 914014);
            }

            $this->invokeMethod([$this, $method], [$value]);
        }
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
     * @return object
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * Returns all annotation associated with doc type annotation.
     * 
     * @return mixed
     */
    public function getDoc($name)
    {
        return isset($this->doc[$name]) ? $this->doc[$name] : false;
    }

    /**
     * Returns only get parameter list.
     * 
     * @return object
     */
    public function getOnlyget()
    {
        return $this->onlyGet;
    }

    /**
     * Returns only post parameter list.
     * 
     * @return object
     */
    public function getOnlypost()
    {
        return $this->onlyPost;
    }

    /**
     * Returns required get parameter list.
     * 
     * @return object
     */
    public function getRequiredget()
    {
        return $this->requiredGet;
    }

    /**
     * Returns required post parameter list.
     * 
     * @return object
     */
    public function getRequiredpost()
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
    protected function setRoute($route)
    {
        $this->route = new Route(
                $this->class, $this->method, $route, $this->controller, array_key_exists('placeholder', $this->annotation));
    }

    /**
     * Sets special value.
     * 
     * @param   array                           $special
     * @throws  InvalidAnnotationExecption
     */
    protected function setPlaceholder($special)
    {
        if (!$this->getRoute()) {
            throw new InvalidAnnotationExecption('Annotation [placeholder]'
                    . ' requires its dependent annotation [route].', $this->class, $this->method, 914015);
        }

        $this->placeholder = new Placeholder($this->class, $this->method, $special, $this->route);
    }

    /**
     * Sets doc annotation information.
     * 
     * @param   array   $doc
     */
    protected function setDoc($doc)
    {
        $this->doc[$doc] = new Doc($this->class, $this->method, $doc, $this->annotation['doc_' . $doc]);
    }

    /**
     * Sets only get parameter validation.
     * 
     * @param   array   $parameters
     */
    protected function setOnlyget($parameters)
    {
        $this->onlyGet = new OnlyGet($this->class, $this->method, $parameters);
    }

    /**
     * Sets only post parameter validation.
     * 
     * @param   array   $parameters
     */
    protected function setOnlypost($parameters)
    {
        $this->onlyPost = new OnlyPost($this->class, $this->method, $parameters);
    }

    /**
     * Sets required get parameter validation.
     * 
     * @param   array   $parameters
     */
    protected function setRequiredget($parameters)
    {
        $this->requiredGet = new RequiredGet($this->class, $this->method, $parameters);
    }

    /**
     * Sets required post parameter validation.
     * 
     * @param   array   $parameters
     */
    protected function setRequiredpost($parameters)
    {
        $this->requiredPost = new RequiredPost($this->class, $this->method, $parameters);
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
    protected function setResponse($response)
    {
        $this->response = new Response($this->class, $this->method, $response);
    }

    /**
     * Sets session scope.
     * 
     * @param array $sessionscope
     */
    protected function setNamedscope($sessionscope)
    {
        $this->namedscope = new NamedScope($this->class, $this->method, $sessionscope);
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
    protected function setForwarder($forwarder)
    {
        $this->forwarder = new Forwarder($this->class, $this->method, $forwarder);
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
    protected function setPriority($priority)
    {
        $this->priority = new Priority($this->class, $this->method, $priority);
    }

    /**
     * Returns exception handler annotation.
     * 
     * @return \Nishchay\Controller\Annotation\ExceptionHandler
     */
    public function getExceptionhandler()
    {
        return $this->exceptionHandler;
    }

    /**
     * Sets exception handler annotation.
     * 
     * @param array $exceptionhandler
     */
    public function setExceptionhandler($exceptionhandler)
    {
        $this->exceptionHandler = new ExceptionHandler($this->class, $this->method, $exceptionhandler);
    }

    /**
     * 
     * @return \Nishchay\Event\Annotation\BeforeEvent
     */
    public function getBeforeevent()
    {
        return $this->beforeEvent;
    }

    /**
     * 
     * @return \Nishchay\Event\Annotation\AfterEvent
     */
    public function getAfterevent()
    {
        return $this->afterEvent;
    }

    /**
     * 
     * @param type $parameter
     */
    protected function setBeforeevent($parameter)
    {
        $this->beforeEvent = new BeforeEvent($this->class, $this->method, $parameter);
    }

    /**
     * 
     * @param type $parameter
     */
    protected function setAfterevent($parameter)
    {
        $this->afterEvent = new AfterEvent($this->class, $this->method, $parameter);
    }

    /**
     * Returns web service annotation.
     * 
     * @return \Nishchay\Service\Annotation\Service|boolean
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
    public function setService($service)
    {
        $this->service = new Service($service, $this->class, $this->method);
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
    public function setNoservice($noservice)
    {
        if ($noservice !== false) {
            throw new InvalidAnnotationExecption('Annotation [NoService] does'
                    . ' not support parameters.', $this->class, $this->method, 914016);
        }
        $this->noservice = true;
        return $this;
    }

}
