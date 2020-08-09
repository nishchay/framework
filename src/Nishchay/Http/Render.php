<?php

namespace Nishchay\Http;

use Nishchay\Http\Request\RequestStore;

/**
 * Renders views just by including file.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Render
{

    /**
     * Includes given file.
     * 
     * @param string $file
     */
    public static function view(string $file)
    {
        extract(RequestStore::getAll());
        include $file;
    }

}
