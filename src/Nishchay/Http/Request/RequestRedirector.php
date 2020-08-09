<?php

namespace Nishchay\Http\Request;

/**
 * Request redirect class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class RequestRedirector
{

    /**
     * Route path.
     * 
     * @var string 
     */
    private $route;

    /**
     * Initialization.
     * 
     * @param string $route
     */
    public function __construct(string $route)
    {
        $this->setRoute($route);
    }

    /**
     * Return route path.
     * 
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Sets route path.
     * 
     * @param stirng $route
     */
    public function setRoute(string $route)
    {
        $this->route = $route;
    }

}
