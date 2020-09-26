<?php

namespace Nishchay\Route\Pattern;

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
        $annotation = ['route' => ['path' => $path, 'type' => $type]];
        
        $placeholder !== null && $annotation['placeholder'] = $placeholder;
        
        return $annotation;
    }

}
