<?php

namespace Nishchay\Cache;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Processor\AbstractSingleton;
use stdClass;

/**
 * Cache collection
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Collection extends AbstractSingleton
{

    /**
     * Instance of this class.
     * 
     * @var self 
     */
    protected static $instance;

    /**
     * Collections of caches.
     * 
     * @var type 
     */
    private $collection = [];

    /**
     * Default cache config.
     * 
     * @var type 
     */
    private $default;

    /**
     * 
     * @param type $name
     * @return type
     */
    public function get($name = null)
    {
        if ($name === null) {
            if ($this->default !== null) {
                $name = $this->default;
            } else {
                $name = Nishchay::getSetting('cache.default');
                if ($name === false) {
                    throw new ApplicationException('Default cache config missing or invalid.', null, null, 912003);
                }
            }
        }

        if (isset($this->collection[$name])) {
            return $this->collection[$name];
        }

        $config = $this->getConfig($name);
        if ($this->isOffline($config) === true) {
            $config = $this->getOfflineConfig();
        }

        return $this->collection[$name] = new CacheHandler($config);
    }

    /**
     * Returns true if cache is offline.
     * 
     * @param \stdClass $config
     * @return boolean
     */
    private function isOffline($config): bool
    {
        if (Nishchay::getSetting('cache.enable') === false) {
            return true;
        }

        if (isset($config->offline) && $config->offline === true) {
            return true;
        }

        return false;
    }

    /**
     * Returns config.
     * 
     * @param string $name
     * @return stdClass
     */
    private function getConfig($name)
    {
        $config = Nishchay::getSetting('cache.config.' . $name);
        if ($config === false) {
            throw new ApplicationException('Cache config [' . $name . '] not found.', null, null, 912004);
        }
        return $config;
    }

    /**
     * Returns config for offline cache.
     * 
     * @return stdClass
     */
    private function getOfflineConfig(): stdClass
    {
        $config = new \stdClass();
        $config->type = 'offline';
        $config->server = [];
        return $config;
    }

    /**
     * 
     */
    protected function onCreateInstance()
    {
        
    }

}
