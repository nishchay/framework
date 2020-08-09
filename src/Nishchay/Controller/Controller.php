<?php

namespace Nishchay\Controller;

use AnnotationParser;
use ReflectionClass;
use ReflectionMethod;
use Nishchay\Exception\UnableToResolveException;
use Nishchay\Exception\BadRequestException;
use Nishchay\Controller\Annotation\Method\Parameter\Parameter;
use Nishchay\DI\DI;
use Nishchay\Http\Request\Request;

/**
 * Description of Processor
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Controller
{

    /**
     * All system instances
     * 
     * @var array 
     */
    private $systemInstance = ['Request'];

    /**
     * Calling dependency instance to resolve parameter.
     * 
     * @var DI 
     */
    private $DI = null;

    /**
     * 
     * @param type $class
     */
    public function classAnnotation($class)
    {
        $this->processOnlyRequiredAnnotation($class);
    }

    /**
     * Controller function annotation
     */
    public function methodAnnotation($method)
    {
        $this->processOnlyRequiredAnnotation($method);
    }

    /**
     * Processes onlyGet/post and requiredGet/post annotation
     * if any defined on passed class or method.
     * 
     * @param type $instance
     */
    protected function processOnlyRequiredAnnotation($instance)
    {
        # Processing Only_get validation.
        $this->requestParameterOnly($instance->getOnlyget(), 'get');

        # Processing Only_post validation.
        $this->requestParameterOnly($instance->getOnlypost(), 'post');

        # Processing Required_get validation.
        $this->requestParameterRequired($instance->getRequiredget(), "get");

        # Processing Required_post validation.
        $this->requestParameterRequired($instance->getRequiredpost(), "post");
    }

    /**
     * Common method for processing onlyGet and onlyPost annotation
     * All method within controller where this type of annotation found 
     * forces that controller method to be call only if these parameter found
     * in requesting URL. There must not be more or less than mentioned Request
     * parameter
     * 
     * @param   instnace      $type
     * @return  NULL
     */
    protected function requestParameterOnly($annotation, $type)
    {
        if ($annotation === false) {
            return false;
        }

        # Requirement parameter as defined in annotation
        $requirement = $annotation->getParameter();
        $received = array_keys($type === 'get' ? Request::get() : Request::post());

        # Directly throwing error if receieved request parameter count differ from
        # requirement parameter count.
        if (count($received) !== count($requirement)) {
            goto NOTFOUND;
        }


        # At this point, we always have exact number of parameter.
        # We will check for received parameter be exist in requirement.
        # If any parameter not found in requirement, we will update flag and 
        # break the loop.
        $found = true;
        foreach ($received as $key) {
            if (!in_array($key, $requirement)) {
                $found = false;
                break;
            }
        }

        if (!$found) {
            NOTFOUND:
            $this->notRequestParameter($annotation->getParameters());
        }
    }

    /**
     * Common method for processing requiredGet and requiredPost annotation
     * This is same only_ type of annotation but these type annotation allows
     * any other request parameter
     * 
     * @param object $type
     * @return NULL
     */
    protected function requestParameterRequired($annotation, $type)
    {
        if ($annotation === false) {
            return false;
        }
        $request = ($type === 'get') ? Request::get() : Request::post();

        # We are iterating over requirement parameter to find in received 
        # parameter. If any not found we process required action.
        foreach ($annotation->getParameter() as $value) {
            if (!array_key_exists($value, $request)) {
                $this->notRequestParameter($annotation->getParameters());
            }
        }
    }

    /**
     * Prepares Controller method's parameter by assigning applicable values
     * if parameter have default value, it's value taken as annotation and 
     * then processed to assign applicable value else object will be assigned
     * by resolving dependency.
     * 
     * @param ReflectionMethod $reflection
     */
    public function prepareMethodParameter(ReflectionMethod $reflection)
    {
        $parameter = [];
        foreach ($reflection->getParameters() as $param) {
            $name = $param->name;

            # If the type hint is exist we will create instance by resolving 
            # dependency
            if (($hinting = $param->getClass()) !== null) {
                $parameter[$name] = $this->getResolvedHinting($hinting);
                continue;
            }

            if ($param->isOptional() === true) {
                $value = $this->processDefaultProperty($param->getDefaultValue()
                        . PHP_EOL, $reflection, $name);
            } else {
                $value = $this->processRequiredProperty($name, $reflection);
            }

            $parameter[$name] = $value;
        }
        return $parameter;
    }

    /**
     * Resolves type hint by creating new instance.
     * NOTE : need improvement. Controller class should not be there.
     * 
     * @param   ReflectionClass $reflection
     * @return  object
     */
    public function getResolvedHinting(ReflectionClass $reflection)
    {
        $this->DI = $this->DI ?? new DI();
        return $this->DI->create($reflection->name);
    }

    /**
     * Prepares property of Controller class being call
     * 
     * @param object $instance
     */
    public function property($instance)
    {
        $reflection = new ReflectionClass($instance);
        $param = [];
        foreach ($reflection->getProperties() as $property) {
            $name = $property->name;
            $param[$name] = AnnotationParser::getAnnotations($property->getDocComment());
            return $param;
        }
    }

    /**
     * For assigning value to required parameter we here only assigning
     * instance of allowed class parameter name must match any of instance 
     * name to get assigned
     * 
     * @param   string  $name
     * @return  mixed
     */
    protected function processRequiredProperty($name, ReflectionMethod $reflection)
    {
        //when name is same as system instance
        if (in_array($name, $this->systemInstance)) {
            $value = $name::me();
        } else {
            switch ($name) {
                case 'GET':
                    $value = Request::get();
                    break;
                case 'POST':
                    $value = Request::post();
                default:
                    throw new UnableToResolveException('Not able to find '
                            . 'what to assign to [' . $name . ']'
                            . ' parameter.', $reflection->class, $reflection->name, 914025);
            }
        }
        return $value;
    }

    /**
     * Processes default parameter of the method to auto bind value depends
     * annotation defined in default value.
     * 
     * @param   string  $doc
     * @param   string  $reflection
     * @param   string  $name
     * @return  mixed
     */
    protected function processDefaultProperty($doc, ReflectionMethod $reflection, $name)
    {
        $annotation = AnnotationParser::getAnnotations($doc);
        $parsed = new Parameter($reflection->class, $reflection->name, $annotation, $name);
        return $parsed->getAnnotationValue();
    }

    /**
     * When request parameter not found
     * 
     * @param array $parameter
     * @return mixed
     */
    private function notRequestParameter($parameter)
    {
        if (array_key_exists('redirect', $parameter)) {
            Request::redirect($parameter['redirect']);
        } else {
            throw new BadRequestException('Request could not be satisfied.', null, null, 914026);
        }
    }

}
