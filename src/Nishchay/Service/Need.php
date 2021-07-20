<?php

namespace Nishchay\Service;

use Nishchay\Controller\ControllerMethod;
use Nishchay\Http\Request\Request;
use Nishchay\Attributes\Controller\Method\Service as ServiceAttribute;

/**
 * Web service Need class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Need
{

    /**
     *
     * @var ServiceAttribute
     */
    private $service;

    /**
     * Instance of controller method attributes.
     * 
     * @param ControllerMethod $method
     */
    public function __construct(ControllerMethod $method)
    {
        $this->service = $method->getService();
    }

    /**
     * Calls need methods.
     * 
     * @param type $name
     * @param type $arguments
     * @return type
     */
    public function __call($name, $arguments)
    {
        if ($this->service instanceof ServiceAttribute && strpos($name, 'is') === 0) {
            $needed = lcfirst(substr($name, 2));
            $fields = explode(',', Request::get('fields'));
            return in_array($needed, $fields) ||
                    in_array($needed, $this->service->getAlways());
        }
    }

}
