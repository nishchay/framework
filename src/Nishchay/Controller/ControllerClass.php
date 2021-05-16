<?php

namespace Nishchay\Controller;

use Nishchay;
use Exception;
use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Exception\InvalidAnnotationParameterException;
use ReflectionClass;
use ReflectionMethod;
use Nishchay\Utility\Coding;
use Nishchay\Controller\ControllerMethod;
use Nishchay\Route\Annotation\Routing;
use Nishchay\Controller\Annotation\ExceptionHandler;
use Nishchay\Attributes\Controller\Method\Response;
use Nishchay\Attributes\Controller\Controller as ControllerAttribute;
use Nishchay\Attributes\AttributeTrait;
use Nishchay\Attributes\Event\{
    AfterEvent,
    BeforeEvent
};
use Nishchay\Attributes\Controller\{
    OnlyGet,
    OnlyPost,
    RequiredGet,
    RequiredPost
};

/**
 * Controller annotation
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ControllerClass
{

    use AttributeTrait;

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
    private $attributes;

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
     * @param   array       $attributes
     * @param   string      $parent
     */
    public function __construct(string $class, array $attributes, string $parent)
    {
        $this->setClass($class);
        $this->attributes = $attributes;
        $this->processAttributes($this->attributes);
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
        return $this->attributes;
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
    protected function setController(ControllerAttribute $controller)
    {
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
                $attributes = $method->getAttributes();
            } catch (Exception $e) {
                throw new InvalidAnnotationExecption($e->getMessage(),
                                $method->class, $method->name, $e->getCode());
            }

            # Now here we are passing it to controller method annotation
            # class so that it valid validates each annotation. Then we
            # we will add this method annotation to this class registry.
            $controllerMethod = new ControllerMethod($method->class,
                    $method->name, $attributes, $this);

            if ($controllerMethod->getRoute() !== null) {
                if ($reflection->isAbstract() === true) {

                    if ($controllerMethod->getPlaceholder() !== null) {
                        throw new ApplicationException('Placeholders are not allowed for abstract route.',
                                        $this->class, $method->name, 914029);
                    }

                    $response = $controllerMethod->getResponse();
                    if (strtolower($response->getType()) !== Response::VIEW_RESPONSE) {
                        throw new ApplicationException('Response type must be [view] for abstract route.',
                                        $this->class, $method->name, 914030);
                    }

                    if ($method->isAbstract() === false) {
                        throw new ApplicationException('In abstract controller only abstract routes are allowed.',
                                        $this->class, $method->name, 914031);
                    }
                }

                $this->addMethod($method->name, $controllerMethod);
            }
        }
    }

    /**
     * Sets Only GET annotation.
     * 
     * @param OnlyGet $onlyGet
     */
    protected function setOnlyGet(OnlyGet $onlyGet)
    {
        $this->onlyget = $onlyGet
                ->setClass($this->class);
    }

    /**
     * Sets Only POST annotation.
     * 
     * @param OnlyPost $onlyPost
     */
    protected function setOnlyPost(OnlyPost $onlyPost)
    {
        $this->onlypost = $onlyPost
                ->setClass($this->class);
    }

    /**
     * Sets required GET annotation.
     * 
     * @param RequiredGet $requiredGet
     */
    protected function setRequiredGet(RequiredGet $requiredGet)
    {
        $this->requiredget = $requiredGet
                ->setClass($this->class);
    }

    /**
     * Sets required  POST annotation.
     * 
     * @param RequiredPost $requiredPost
     */
    protected function setRequiredpost(RequiredPost $requiredPost)
    {
        $this->requiredpost = $requiredPost
                ->setClass($this->class);
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
     * @return BeforeEvent
     */
    public function getBeforeEvent()
    {
        return $this->beforeevent;
    }

    /**
     * 
     * @return AfterEvent
     */
    public function getAfterEvent()
    {
        return $this->afterevent;
    }

    /**
     * Sets before event for controller class.
     * 
     * @param BeforeEvent $beforeEvent
     */
    protected function setBeforeEvent(BeforeEvent $beforeEvent)
    {
        $this->beforeevent = $beforeEvent->setClass($this->class);
    }

    /**
     * Sets after event for controller class.
     * 
     * @param AfterEvent $afterEvent
     */
    protected function setAfterEvent(AfterEvent $afterEvent)
    {
        $this->afterevent = $afterEvent->setClass($this->class);
    }

}
