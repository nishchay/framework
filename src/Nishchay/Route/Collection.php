<?php

namespace Nishchay\Route;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Persistent\System;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Attributes\Controller\Method\Placeholder;
use Nishchay\Attributes\Controller\Method\Route;
use Nishchay\Processor\AbstractCollection;
use Nishchay\Route\Visibility;

/**
 * Route collection class stores all route defined.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Collection extends AbstractCollection
{

    /**
     * Collection.
     * 
     * @var array 
     */
    private $collection = [];

    /**
     * This also stores all routes defined with request method.
     * Used to find route confliction.
     * 
     * @var array 
     */
    private $routeDefinitions = [];

    /**
     * Current pointer index of collection.
     * 
     * @var int 
     */
    private $position = 0;

    /**
     * Sorted flag.
     * 
     * @var boolean 
     */
    private $sorted = false;

    /**
     * Initialization
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Saves instance to route persistent file if application in live stage
     * and updates flag to store mode.
     * 
     * @return NULL
     */
    private function init()
    {
        if (Nishchay::isApplicationStageLive() && System::isPersisted('routes')) {
            $this->collection = System::getPersistent('routes');
        }
    }

    /**
     * Returns current element of collection.
     * For every call,pointer moves to next element of collection.
     * 
     * @return boolean|array
     */
    public function get()
    {
        return $this->collection;
    }

    /**
     * Returns regex form of route.
     * 
     * @param   array       $find
     * @param   array       $replace
     * @param   string      $subject
     * @return  string
     */
    private function getPregex($find, $replace, $subject)
    {
        # Replacement for optional segment
        $subject = preg_replace_callback('#\/\?([^\/]*)#', function($match) {
            return "(/{$match[1]})?";
        }, $subject);
        return preg_replace($find, $replace, str_replace('/', '\\/', $subject));
    }

    /**
     * Stores route into collection.
     * 
     * @param   \Nishchay\Route\Annotation\Placeholder      $placeholder
     * @param   \Nishchay\Route\Annotation\Route        $route
     * @param   int                                   $priority
     * @return  NULL
     * @throws  \Exception
     */
    public function store($placeholder, Route $route, $priority)
    {
        if (Nishchay::getControllerCollection()->getClass($route->getClass()) === false) {
            return;
        }

        $this->checkStoring();
        if ((!$route instanceof Route) || ($placeholder !== null && !$placeholder instanceof Placeholder)) {
            throw new ApplicationException('First two paramter should be object.', null, null, 926010);
        }

        # Route instance.
        $collection['object'] = $route;

        # Defined route.
        $path = $route->getPath();
        $collection['priority'] = $priority;

        # When route has defined special segment in  it. We should replace it 
        # with actual expresion. We will store bothe nammed regex(urlex) and 
        # noraml(pattern). URLEX is used to get segment value. 
        if ($placeholder instanceof Placeholder) {
            $placeholderValues = $placeholder->getPlaceholder();
            $keys = array_keys($placeholderValues);
            $collection['urlex'] = $this->getPregex($keys, array_values($placeholderValues), $path);
            $pattern = $this->getPregex($keys, array_values($placeholder->getPattern()), $path);
        } else {
            $collection['urlex'] = $path;
            $pattern = $path;
        }

        $this->storeDefinition($route, $pattern, $path);
        $this->collection[] = $collection;
    }

    /**
     * 
     * @param   \Nishchay\Route\Annotation\Route                 $route
     * @param   string                                          $pattern
     * @param   string                                          $path
     * @throws  ApplicationException
     */
    private function storeDefinition(Route $route, $pattern, $path)
    {
        if (empty(($requestMethods = $route->getType()))) {
            $requestMethods = $route->getValidRequestMethods();
        }

        # Checking if route with same request method already created or not.
        if ($this->isRouteDefinitionExist($pattern) &&
                $this->isRequestMethodAlreadyExist($requestMethods, $pattern)) {
            throw new ApplicationException('Route [' . $path . '] '
                    . 'conflicted.', $route->getClass(), $route->getMethod(), 926011);
        }

        # If route definition already exist, will merge current route request
        # method with already created.
        if ($this->isRouteDefinitionExist($pattern)) {
            $requestMethods = array_merge($this->routeDefinitions[$pattern], $requestMethods);
        }

        $this->routeDefinitions[$pattern] = $requestMethods;
    }

    /**
     * Returns TRUE if route definition already exist.
     * 
     * @param string $pattern
     * @return boolean
     */
    private function isRouteDefinitionExist($pattern)
    {
        return array_key_exists($pattern, $this->routeDefinitions);
    }

    /**
     * Returns TRUE if request methods already exist passed route pattern.
     * 
     * @param array $requestMethods
     * @param string $pattern
     * @return boolean
     */
    private function isRequestMethodAlreadyExist($requestMethods, $pattern)
    {
        return empty(array_intersect($requestMethods, $this->routeDefinitions[$pattern])) === false;
    }

    /**
     * Sorts all routes based on priority number.
     * 
     * @return NULL
     */
    public function sort()
    {
        # Just to prevent re sorting.
        if ($this->sorted) {
            return false;
        }

        # Let's sort it.
        if (!empty($this->collection)) {
            ArrayUtility::multiSort($this->collection, 'priority', 'desc');
        }

        if (Nishchay::isApplicationStageLive()) {
            System::setPersistent('routes', $this->collection);
        }
        
        $this->sorted = true;
    }

    /**
     * Moves current pointer to first element of collection.
     * 
     */
    public function reset()
    {
        $this->position = 0;
    }

    /**
     * Returns total number of defined routes in an application.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * Returns list of services defined in an application.
     * 
     * @return array
     */
    public function getServices()
    {
        $services = [];
        foreach ($this->collection as $route) {
            $method = Nishchay::getControllerCollection()
                    ->getMethod("{$route['object']->getClass()}::"
                    . "{$route['object']->getMethod()}");
            if ($method->getService() !== false) {
                $services[] = $route['object'];
            }
        }
        return $services;
    }

    /**
     * Returns total number of services defined in an application.
     * 
     * @return int
     */
    public function getServiceCount()
    {
        return count($this->getServices());
    }

    /**
     * Matches request string with all defined route and then it matches
     * requested method with matched route supported request method.
     * If both condition satisfy, matched route will be returned.
     * 
     * @param string $urlString
     * @param string $requestMethod
     * @return boolean
     */
    public function getRoute($urlString, $requestMethod, $all = false)
    {
        $matched = [];
        foreach ($this->collection as $value) {
            if (preg_match('#^' . $value['urlex'] . '$#', $urlString, $match)) {

                # Even if route string has been matched, we still need to match 
                # request method if it is defined on route.
                if ($all === false && !$this->isRequestAllowed(
                                $requestMethod, $value['object']->getType(), $value['object']
                        )) {
                    continue;
                }

                # If route defintion has defined placeholder segment in it. We will
                # first find placeholder segemnt and then we will add it to
                # response.
                $value['segment'] = [];
                foreach ($match as $segment => $found) {
                    if (!is_int($segment)) {
                        $value['segment'][$segment] = $found;
                    }
                }
                $value['context'] = Nishchay::getControllerCollection()
                        ->getContext(
                        $value['object']->getClass()
                );

                if ($all === false) {
                    return $value;
                }
                $matched[] = $value;
            }
        }

        return empty($matched) ? false : $matched;
    }

    /**
     * Return false when current request does not match with current requested route.
     * If the current request route does not specify any type then this method always return TRUE.
     * 
     * @param   string      $currentType
     * @param   array       $supportedType
     * @return  boolean
     */
    protected function isRequestAllowed(string $currentType, $supportedType, Route $route)
    {
        if ($this->isVisible($route) === false) {
            return false;
        }
        if (!empty($supportedType) && !in_array($currentType, $supportedType)) {
            return false;
        }

        if (!empty($route->getStage()) && !in_array(Nishchay::getApplicationStage(), $route->getStage())) {
            return false;
        }

        return true;
    }

    /**
     * Checks routes visibility.
     */
    private function isVisible(Route $route)
    {
        return Visibility::getInstance()->check($route);
    }

    /**
     * Returns routes by matching with its path as defined in @Route annotation.
     * 
     * @param string $name
     * @return array
     */
    public function getByName($name, $pattern = false)
    {
        $matched = [];

        foreach ($this->collection as $route) {
            if ($pattern === false && $route['object']->getPath() === $name) {
                $matched[] = $route;
            } else if ($pattern === true && preg_match($name, $route['object']->getPath())) {
                $matched[] = $route;
            }
        }

        return $matched;
    }

}
