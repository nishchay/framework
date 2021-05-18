<?php

namespace Nishchay\Route\Pattern;

use Nishchay;
use ReflectionMethod;
use Nishchay\Processor\VariableType;
use Nishchay\Attributes\Controller\Method\{
    Route,
    Placeholder
};

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
        if (empty($route)) {
            return null;
        }

        $reflection = new ReflectionMethod($class, $method);
        $placeholders = $segments = [];
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type === null || in_array($type->getName(),
                            [VariableType::STRING, VariableType::INT, VariableType::BOOL, VariableType::DATA_ARRAY]) === false) {
                break;
            }

            $placeholderValue = $type->getName();
            $optional = ($type->allowsNull() || $parameter->isOptional()) ? '?' : '';
            if ($type->getName() === VariableType::DATA_ARRAY) {
                if ($parameter->isOptional() === false) {
                    break;
                }

                $default = $parameter->getDefaultValue();
                if (!empty($default)) {
                    $placeholderValue = $default;
                }

                $type->allowsNull() === false && $optional = '';
            }

            $placeholders[$parameter->getName()] = $placeholderValue;
            $segments[] = $optional . '{' . $parameter->getName() . '}';
        }
        if (count($placeholders) > 0) {
            $postfix = implode('/', $segments);
            $path = trim($route->getPath(), '/') . '/' . $postfix;
            $newRoute = new Route($path, $route->getType(), $route->getPrefix(),
                    $route->getIncoming(), $route->getStage());
            $placeholder = new Placeholder($placeholders);
            $route = ['route' => $newRoute, 'placeholder' => $placeholder];
        }
        return $route;
    }

}
