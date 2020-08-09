<?php

namespace Nishchay\Lang;

use Nishchay;
use Nishchay\Processor\AbstractSingleton;

/**
 * Lang collection class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Collection extends AbstractSingleton
{

    /**
     * Instance of this class.
     * 
     * @var \Nishchay\Lang\Lang 
     */
    protected static $instance;

    /**
     * List of translations.
     * 
     * @var array
     */
    private $collection = [];

    /**
     * Initialization
     */
    protected function onCreateInstance()
    {
        // NOT REQUIRED FOR NOW
    }

    /**
     * Stores namespace.
     * 
     * @param type $namespace
     * @param type $main
     * @param type $default
     * @return boolean
     */
    public function store($namespace, $main, $default)
    {
        if (array_key_exists($namespace, $this->collection)) {
            return false;
        }
        $this->prepareTranslation($namespace, $main, $default);
    }

    /**
     * Returns translation namespace.
     * 
     * @param type $namespace
     * @return \Nishchay\Lang\Translations
     */
    public function get($namespace)
    {
        return $this->collection[$namespace];
    }

    /**
     * Loads translation and prepares it.
     * 
     * @param string $namespace
     * @param type $main
     * @param type $default
     */
    private function prepareTranslation($namespace, $main, $default)
    {
        # Fetching translations of main locale.
        $mainTrasnlations = $this->getTranslation($namespace, $main);
        $defaultTranslation = [];

        # No need when main and default translation is same.
        if ($main !== $default) {
            $defaultTranslation = $this->getTranslation($namespace, $default);
        }

        if (empty($namespace)) {
            $namespace = '';
        }
        $this->collection[$namespace] = new Translations(array_merge($defaultTranslation, $mainTrasnlations));
    }

    /**
     * Returns translation array of given locale name with namespace.
     * 
     * @param string $locale
     * @return array
     * @throws ApplicationException
     */
    private function getTranslation($namespace, $locale)
    {
        # When no namepsace passed we will consider local as namespace.
        # This way local/local.php will be resolved.
        if (empty($namespace)) {
            $namespace = $locale;
        }

        return Nishchay::getLang()->getLoader()->getTranslations($namespace, $locale);
    }

    /**
     * Clears all loaded translations. 
     */
    public function reset()
    {
        $this->collection = [];
    }

}
