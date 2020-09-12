<?php

namespace Nishchay\Route\Pattern;

use ReflectionMethod;

/**
 * Action method route pattern.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.1
 * @author      Bhavik Patel
 */
class ActionMethodParameter extends ActionMethod
{

    public function __construct()
    {
        parent::__construct();
        $this->patternName = 'actionMethodParameter';
    }

    /**
     * 
     * @param string $class
     * @param string $method
     * @return type
     */
    public function processMethod(string $class, string $method)
    {
        $route = parent::processMethod($class, $method);

        $reflection = new ReflectionMethod($class, $method);
        $placeholders = $segments = [];
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type === null || $parameter->getType()->isBuiltIn() === false) {
                break;
            }

            $placeholders[$parameter->getName()] = $type->getName();
            $optional = ($type->allowsNull() || $parameter->isOptional()) ? '?' : '';
            $segments[] = $optional . '{' . $parameter->getName() . '}';
        }

        if (count($placeholders) > 0) {
            $postfix = implode('/', $segments);
            $route['path'] = trim($route['path'], '/') . $postfix;
            $route = ['route' => $route, 'placeholder' => $placeholders];
        }

        return $route;
    }

}
