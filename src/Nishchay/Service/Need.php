<?php

namespace Nishchay\Service;

use Nishchay\Controller\Annotation\Method\Method;
use Nishchay\Http\Request\Request;
use Nishchay\Service\Annotation\Service as ServiceAnnotation;

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
     * @var \Nishchay\Service\Annotation\Service 
     */
    private $service;

    /**
     * Instance of controller method annotations.
     * 
     * @param Method $method
     */
    public function __construct(Method $method)
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
        if ($this->service instanceof ServiceAnnotation && strpos($name, 'is') === 0) {
            $needed = lcfirst(substr($name, 2));
            $fields = explode(',', Request::get('fields'));
            return in_array($needed, $fields) ||
                    in_array($needed, $this->service->getAlways());
        }
    }

}
