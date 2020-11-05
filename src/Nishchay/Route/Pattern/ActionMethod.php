<?php

namespace Nishchay\Route\Pattern;

use Nishchay\Route\Annotation\Route;

/**
 * Action method route pattern.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.1
 * @author      Bhavik Patel
 */
class ActionMethod extends AbstractPattern
{

    /**
     * Regex pattern to extract route from method name.
     * 
     * @var string 
     */
    private $regex;

    /**
     * Initialization
     */
    public function __construct()
    {
        parent::__construct('actionMethod');
        $this->setRegex();
    }

    /**
     * Sets regex pattern for extracting route from method name.
     */
    private function setRegex()
    {
        $types = array_map(function($type) {
            return ucfirst(strtolower($type));
        }, Route::REQUEST_METHODS);
        $this->regex = '#^action(?P<type>((' . implode('|', $types) . ')*)?)(.*)#';
    }

    /**
     * Processes method to find route path.
     * 
     * @param string $class
     * @param string $method
     * @return array
     */
    public function processMethod(string $class, string $method)
    {
        preg_match($this->regex, $method, $match);
        if (empty($match)) {
            return null;
        }

        list(, $type) = $match;

        $path = lcfirst(end($match));
        $route = ['path' => $path === 'index' ? '/' : $path];
        if (strlen($type) > 0) {
            $route['type'] = preg_split('/(?=[A-Z])/', lcfirst($type));
        }

        return $route;
    }

}
