<?php

namespace Nishchay\Persistent;

use Nishchay;
use Nishchay\Utility\SystemUtility;
use Nishchay\FileManager\SimpleFile;

/**
 * Persistent class used to store object and get persisted objects.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class System
{

    /**
     * Returns path of persistent file given name.
     * 
     * @param   string      $name
     * @return  string
     */
    public static function getPath($name)
    {
        return SystemUtility::refactorDS(PERSISTED . 'version' . DS
                        . (Nishchay::getApplicationVersion()) . DS
                        . "{$name}.nsch");
    }

    /**
     * Returns TRUE if application stage is LIVE and application data is
     * persisted.
     * 
     * @param   string      $name
     * @return  boolean
     */
    public static function isPersisted($name)
    {
        return Nishchay::isApplicationStageLive() &&
                file_exists(self::getPath($name));
    }

    /**
     * Returns persisted application data of given name.
     * 
     * @param   string      $name
     * @return  mixed
     */
    public static function getPersistent($name)
    {
        $peristed_name = self::getPath($name);
        return unserialize(file_get_contents($peristed_name));
    }

    /**
     * Persist application data of given name.
     * 
     * @param   string      $name
     * @param   string      $content
     */
    public static function setPersistent($name, $content = '')
    {
        if (Nishchay::isApplicationStageLive()) {
            $peristed_name = self::getPath($name);
            $file = new SimpleFile($peristed_name, SimpleFile::TRUNCATE_WRITE);
            $file->write(serialize($content));
        }
    }

}
