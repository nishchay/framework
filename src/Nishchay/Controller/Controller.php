<?php

namespace Nishchay\Controller;

use Nishchay;
use AnnotationParser;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Nishchay\Exception\UnableToResolveException;
use Nishchay\Exception\BadRequestException;
use Nishchay\Controller\Annotation\Method\Parameter\Parameter;
use Nishchay\DI\DI;
use Nishchay\Http\Request\Request;
use Nishchay\Processor\VariableType;

/**
 * Class for validating only and required annotation and assigning values to
 * controller property and method
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Controller
{

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
        $this->requestParameterOnly($instance->getOnlyGet(), Request::GET);

        # Processing Only_post validation.
        $this->requestParameterOnly($instance->getOnlyPost(), Request::POST);

        # Processing Required_get validation.
        $this->requestParameterRequired($instance->getRequiredGet(), Request::GET);

        # Processing Required_post validation.
        $this->requestParameterRequired($instance->getRequiredPost(), Request::POST);
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
    protected function requestParameterOnly($attribute, $type)
    {
        if (empty($attribute)) {
            return false;
        }

        # Requirement parameter as defined in annotation
        $requirement = $attribute->getParameter();
        $received = array_keys($type === Request::GET ? Request::get() : Request::post());

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
            $this->notRequestParameter($attribute);
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
    protected function requestParameterRequired($attribute, $type)
    {
        if (empty($attribute)) {
            return false;
        }
        $request = ($type === Request::GET) ? Request::get() : Request::post();

        # We are iterating over requirement parameter to find in received 
        # parameter. If any not found we process required action.
        foreach ($attribute->getParameter() as $value) {
            if (!array_key_exists($value, $request)) {
                $this->notRequestParameter($attribute);
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
        $placeholder = Nishchay::getControllerCollection()
                ->getMethod($reflection->class . '::' . $reflection->name)
                ->getPlaceholder();
        $parameters = [];
        foreach ($reflection->getParameters() as $param) {
            $name = $param->name;

            # If the type hint is exist we will create instance by resolving 
            # dependency
            $paramType = $param->getType()?->getName();
            if ($paramType !== null && class_exists($paramType)) {
                $parameters[$name] = $this->getResolvedHinting($paramType);
                continue;
            }

            if ($param->isOptional() === true) {
                $value = $this->processDefaultProperty($param, $reflection, $placeholder);
            } else {
                $value = $this->processRequiredProperty($param, $reflection, $placeholder);
            }

            if ($paramType === VariableType::DATA_ARRAY && is_array($value) === false) {
                $value = (array) $value;
            }

            $parameters[$name] = $value;
        }
        return $parameters;
    }

    /**
     * Resolves type hint by creating new instance.
     * NOTE : need improvement. Controller class should not be there.
     * 
     * @param   ReflectionClass $class
     * @return  object
     */
    public function getResolvedHinting($class)
    {
        $this->DI = $this->DI ?? new DI();
        return $this->DI->create($class);
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
    protected function processRequiredProperty(ReflectionParameter $parameter, ReflectionMethod $reflection, $placeholder)
    {
        $name = $parameter->getName();
        switch ($name) {
            case Request::GET:
                $value = Request::get();
                break;
            case Request::POST:
                $value = Request::post();
                break;
            default:
                if (($value = $this->getFromRequest($name, $placeholder)) !== false) {
                    return $value;
                }

                if ($parameter->getType() !== null && $parameter->getType()->allowsNull()) {
                    return null;
                }

                throw new UnableToResolveException('Not able to find '
                                . 'what to assign to [' . $name . ']'
                                . ' parameter.', $reflection->class, $reflection->name, 914025);
        }
        return $value;
    }

    /**
     * Returns value from request.
     * 
     * @param type $name
     * @return mixed
     */
    private function getFromRequest($name, $placeholder)
    {

        # If parameter name exists in segment we will return it from segment only.
        # When segment is optional and it does not exists in url path then
        # Request::segment returns false, if we don't do this then it result in
        # fetching from GET or POST parameter.
        if ($placeholder && $placeholder->getPlaceholderType($name) !== false) {
            return Request::segment($name);
        }
        $from = ['segment', 'file', 'post', 'get'];
        foreach ($from as $fromName) {
            if (($value = Request::{$fromName}($name)) !== false) {
                return $value;
            }
        }

        return false;
    }

    /**
     * Processes default parameter of the method to auto bind value depends
     * annotation defined in default value.
     * 
     * @param   ReflectionParameter  $parameter
     * @param   string  $reflection
     * @param   \Nishchay\Route\Annotation\Placeholder  $placeholder
     * @return  mixed
     */
    protected function processDefaultProperty(ReflectionParameter $parameter, ReflectionMethod $reflection, $placeholder)
    {
        $default = $parameter->getDefaultValue();
        $name = $parameter->name;

        if (is_scalar($default)) {
            $doc = $parameter->getDefaultValue() . PHP_EOL;
            $annotation = AnnotationParser::getAnnotations($doc);
        }
        # There's no annotation?.
        if (empty($annotation)) {
            if (($value = $this->getFromRequest($name, $placeholder)) !== false) {
                return $value;
            }

            return $parameter->getDefaultValue();
        }
        $parsed = new Parameter($reflection->class, $reflection->name, $annotation, $name);
        return $parsed->getAnnotationValue();
    }

    /**
     * When request parameter not found
     * 
     * @param array $attribute
     * @return mixed
     */
    private function notRequestParameter($attribute)
    {
        if ($attribute->getRedirect() !== null) {
            Request::redirect($attribute->getRedirect());
        } else {
            throw new BadRequestException('Request could not be satisfied.', null, null, 914026);
        }
    }

}
