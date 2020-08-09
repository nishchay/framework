<?php

namespace Nishchay\Controller\Annotation;

use Nishchay;
use AnnotationParser;
use Exception;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Exception\InvalidAnnotationParameterException;
use ReflectionClass;
use ReflectionMethod;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Utility\Coding;
use Nishchay\Controller\Annotation\Method\Method as ControllerMethodAnnotation;
use Nishchay\Route\Annotation\Routing;
use Nishchay\Event\Annotation\AfterEvent;
use Nishchay\Event\Annotation\BeforeEvent;
use Nishchay\Controller\Annotation\ExceptionHandler;

/**
 * Controller annotation
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Controller extends BaseAnnotationDefinition
{

    /**
     * All method of the controller.
     * 
     * @var array 
     */
    private $methods = [];

    /**
     * All annotation defined on controller.
     *  
     * @var array 
     */
    private $annotation;

    /**
     * Controller annotation.
     * 
     * @var boolean 
     */
    private $controller = false;

    /**
     * Routing annotation.
     * 
     * @var \Nishchay\Route\Annotation\Routing 
     */
    private $routing = false;

    /**
     * Only GET annotation.
     * 
     * @var \Nishchay\Controller\Annotation\OnlyGet 
     */
    private $onlyget = false;

    /**
     * Only POST annotation.
     * 
     * @var \Nishchay\Controller\Annotation\OnlyPost
     */
    private $onlypost = false;

    /**
     * Required GET annotation.
     * 
     * @var \Nishchay\Controller\Annotation\RequiredGet 
     */
    private $requiredget = false;

    /**
     * Required POST annotation.
     * 
     * @var \Nishchay\Controller\Annotation\RequiredPost 
     */
    private $requiredpost = false;

    /**
     *
     * @var \Nishchay\Event\Annotation\BeforeEvent
     */
    private $beforeevent = false;

    /**
     *
     * @var \Nishchay\Event\Annotation\AfterEvent 
     */
    private $afterevent = false;

    /**
     * Exception handler annotation.
     * 
     * @var \Nishchay\Controller\Annotation\ExceptionHandler 
     */
    private $exceptionhandler = false;

    /**
     * 
     * @param   string      $class
     * @param   array       $annotation
     */
    public function __construct($class, $annotation, $parent)
    {
        parent::__construct($class, null);
        $this->annotation = $annotation;
        $this->setter($this->annotation);
        Nishchay::getControllerCollection()->store($class, $this, $parent);
        $this->extractRoute();
    }

    /**
     * Returns all annotation defined on the class.
     * 
     * @return array
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }

    /**
     * Returns controller annotation value.
     * 
     * @return boolean
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Returns routing annotation.
     * 
     * @return \Nishchay\Route\Annotation\Routing
     */
    public function getRouting()
    {
        return $this->routing;
    }

    /**
     * Returns Only GET annotation.
     * 
     * @return \Nishchay\Controller\Annotation\OnlyGet
     */
    public function getOnlyget()
    {
        return $this->onlyget;
    }

    /**
     * Returns only POST annotation.
     * 
     * @return \Nishchay\Controller\Annotation\OnlyPost
     */
    public function getOnlypost()
    {
        return $this->onlypost;
    }

    /**
     * Returns required GET annotation.
     * 
     * @return \Nishchay\Controller\Annotation\RequiredGet
     */
    public function getRequiredget()
    {
        return $this->requiredget;
    }

    /**
     * Returns required POST annotation.
     * 
     * @return \Nishchay\Controller\Annotation\RequiredPost
     */
    public function getRequiredpost()
    {
        return $this->requiredpost;
    }

    /**
     * 
     * @param   boolean                         $controller
     * @throws  InvalidAnnotationParameterException
     */
    protected function setController($controller)
    {
        if ($controller !== false) {
            throw new InvalidAnnotationExecption('Annotation [controller]'
                    . ' does not support paramters.',
                    $this->class, $this->method, 914020);
        }

        $this->controller = true;
    }

    /**
     * 
     * @param   array  $routing
     */
    protected function setRouting($routing)
    {
        $this->routing = new Routing($this->class, $routing);
    }

    /**
     * Returns controller method annotation.
     * 
     * @param   string      $method
     * @return  \Nishchay\Controller\Annotation\Method\Method
     */
    public function getMethod($method = null)
    {
        if ($method === null) {
            return $this->methods;
        }

        return array_key_exists($method, $this->methods) ? $this->methods[$method] : false;
    }

    /**
     * Add methods to this controller.
     *  
     * @param   string                                              $method
     * @param   \Nishchay\Controller\Annotation\Method\Method        $object
     */
    protected function addMethod($method = null, $object = null)
    {
        $this->methods[$method] = $object;
    }

    /**
     * Iterate over all methods to find routes.
     * 
     * @throws  InvalidAnnotationExecption
     */
    private function extractRoute()
    {
        $reflection = new ReflectionClass($this->class);

        # Getting Public method only from controller and processing.
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            # We ignore method defined in parent class, starting with underscore
            # or is static.
            if (Coding::isIgnorable($method, $this->class)) {
                continue;
            }

            # Parsing annotation defined on method. If any of method annotation 
            # is invalid, we will catch exception and rethrow to with adding class
            # and method information.
            try {
                $annotation = AnnotationParser::getAnnotations($method->getDocComment());
            } catch (Exception $e) {
                throw new InvalidAnnotationExecption($e->getMessage(),
                        $method->class, $method->name, $e->getCode());
            }

            # Now here we are passing it to controller method annotation
            # class so that it valid validates each annotation. Then we
            # we will add this method annotation to this class registry.
            $methodAnnotation = new ControllerMethodAnnotation($method->class,
                    $method->name, $annotation, $this);
            if ($methodAnnotation->getRoute() !== false) {
                $this->addMethod($method->name, $methodAnnotation);
            }
        }
    }

    /**
     * Sets Only GET annotation.
     * 
     * @param   array   $parameters
     */
    protected function setOnlyget($parameters)
    {
        $this->onlyget = new OnlyGet($this->class, null, $parameters);
    }

    /**
     * Sets Only POST annotation.
     * 
     * @param   array   $parameters
     */
    protected function setOnlypost($parameters)
    {
        $this->onlypost = new OnlyPost($this->class, null, $parameters);
    }

    /**
     * Sets required GET annotation.
     * 
     * @param   array     $parameters
     */
    protected function setRequiredget($parameters)
    {
        $this->requiredget = new RequiredGet($this->class, null, $parameters);
    }

    /**
     * Sets required  POST annotation.
     * 
     * @param   array   $parameters
     */
    protected function setRequiredpost($parameters)
    {
        $this->requiredpost = new RequiredPost($this->class, null, $parameters);
    }

    /**
     * Returns exception handler annotation.
     * 
     * @return \Nishchay\Controller\Annotation\ExceptionHandler
     */
    public function getExceptionhandler()
    {
        return $this->exceptionhandler;
    }

    /**
     * Sets exception handler annotation.
     * 
     * @param array $exceptionhandler
     */
    public function setExceptionhandler($exceptionhandler)
    {
        $this->exceptionhandler = new ExceptionHandler($this->class, null,
                $exceptionhandler);
    }

    /**
     * 
     * @return \Nishchay\Event\Annotation\BeforeEvent
     */
    public function getBeforeevent()
    {
        return $this->beforeevent;
    }

    /**
     * 
     * @return \Nishchay\Event\Annotation\AfterEvent
     */
    public function getAfterevent()
    {
        return $this->afterevent;
    }

    /**
     * 
     * @param type $parameter
     */
    protected function setBeforeevent($parameter)
    {
        $this->beforeevent = new BeforeEvent($this->class, $this->method,
                $parameter);
    }

    /**
     * 
     * @param type $parameter
     */
    protected function setAfterevent($parameter)
    {
        $this->afterevent = new AfterEvent($this->class, $this->method,
                $parameter);
    }

}
