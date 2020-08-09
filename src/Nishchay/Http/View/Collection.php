<?php

namespace Nishchay\Http\View;

use Nishchay;
use Nishchay\Persistent\System as SystemPersistent;

/**
 * View Collection class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Collection
{

    /**
     * Collection array for views.
     * 
     * @var array 
     */
    private static $collection = [];

    /**
     * Stores view into collection.
     * 
     * @param string $path
     */
    public static function store(string $path): bool
    {
        if (!in_array($path, self::$collection)) {
            self::$collection[] = $path;
            return true;
        }
        return false;
    }

    /**
     * Returns all views within collection.
     * 
     * @return array
     */
    public static function get(): array
    {
        if (SystemPersistent::isPersisted('views') && Nishchay::isApplicationStageLive()) {
            self::$collection = SystemPersistent::getPersistent('views');
        }
        return self::$collection;
    }

}
