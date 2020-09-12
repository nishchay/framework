<?php

namespace Nishchay\Route\Pattern;

/**
 * Route pattern collection.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.1
 * @author      Bhavik Patel
 */
class Action extends AbstractPattern
{

    public function __construct()
    {
        parent::__construct('action');
    }

    public function processMethod(string $class, string $method)
    {
        if (strpos($method, 'action') !== 0) {
            return null;
        }
        $path = lcfirst(substr($method, 6));

        return ['path' => $path === 'index' ? '/' : $path];
    }

}
