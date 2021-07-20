<?php

namespace Nishchay\Route\Pattern;

use Nishchay\Attributes\Controller\Method\{
    Route,
    Placeholder
};

/**
 * Abstract pattern.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.1
 * @author      Bhavik Patel
 */
class Crud extends AbstractPattern
{

    const CRUD = [
        'index' => ['/', 'GET', null],
        'create' => ['/', 'POST', null],
        'fetch' => ['{id}', 'GET', ['id' => 'int']],
        'update' => ['{id}', 'PUT', ['id' => 'int']],
        'delete' => ['{id}', 'DELETE', ['id' => 'int']]
    ];

    /**
     * Initialization
     */
    public function __construct()
    {
        parent::__construct('crud');
    }

    /**
     * Builds route.
     * 
     * @param string $class
     * @param string $method
     */
    public function processMethod(string $class, string $method)
    {
        if (isset(self::CRUD[$method]) === false) {
            return null;
        }

        list($path, $type, $placeholder) = self::CRUD[$method];
        $route = new Route($path, $type);

        if ($placeholder !== null) {
            return [
                'route' => $route,
                'placeholder' => new Placeholder($placeholder)
            ];
        }

        return $route;
    }

}
