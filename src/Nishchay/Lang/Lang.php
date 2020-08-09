<?php

namespace Nishchay\Lang;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Processor\AbstractSingleton;
use Nishchay\Lang\Loader\FileLoader;
use Nishchay\Lang\Loader\DBLoader;
use Nishchay\Lang\Loader\LangLoaderInterface;

/**
 * Lang class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Lang extends AbstractSingleton
{

    /**
     * Instance of this class.
     * 
     * @var \Nishchay\Lang\Lang 
     */
    protected static $instance;

    /**
     * Main locale name.
     * @var type 
     */
    private $mainLocale;

    /**
     * Default locale name.
     * 
     * @var type 
     */
    private $defaultLocale;

    /**
     * Lang Loader.
     * 
     * @var Loader\LangLoaderInterface
     */
    private $loader;

    /**
     * Initialization.
     */
    public function onCreateInstance()
    {
        $this->getLoader()->init();
        $config = Nishchay::getSetting('lang');
        if ($config === false || !isset($config->locale->main) ||
                !isset($config->locale->default)) {
            throw new ApplicationException('Lang config is not proper.', null, null, 921006);
        }

        $this->mainLocale = $config->locale->main;
        $this->defaultLocale = $config->locale->default;
    }

    /**
     * Prepares translation
     * 
     * @param type $path
     * @param type $config
     * @throws ApplicationException
     */
    private function loadTranslation($namespace)
    {
        $this->getCollection()
                ->store($namespace, $this->mainLocale, $this->defaultLocale);
    }

    /**
     * Returns translation of given key.
     * If translation contains placeholder it will be replaced by placeholder
     * passed.
     * 
     * @param string $key
     * @return string
     */
    public function line(string $key, array $placeholder = [], $namespace = null)
    {
        if ($namespace !== null && !is_string($namespace)) {
            throw new ApplicationException('Lang line namespace should be string.', 2, null, 921007);
        }
        $this->loadTranslation($namespace);
        try {
            return $this->getCollection()
                            ->get($namespace)
                            ->line($key, $placeholder);
        } catch (ApplicationException $e) {
            throw new ApplicationException($e->getMessage() . ' with'
                    . ' namespace [' . (empty($namespace) ? 'root namespace' : $namespace) . '].', null, null, 921008);
        }
    }

    /**
     * Returns instance of lang collection.
     * 
     * @return \Nishchay\Lang\Collection
     */
    private function getCollection()
    {
        return Collection::getInstance();
    }

    /**
     * Sets main locale.
     * 
     * @param string $name
     */
    public function setMain(string $name)
    {
        $this->mainLocale = $name;
        $this->getCollection()->reset();
    }

    /**
     * Sets default locale.
     * 
     * @param string $name
     */
    public function setDefault(string $name)
    {
        $this->defaultLocale = $name;
        $this->getCollection()->reset();
    }

    /**
     * Returns main local name.
     * 
     * @return string
     */
    public function getMain(): string
    {
        return $this->mainLocale;
    }

    /**
     * Returns default locale name.
     * 
     * @return string
     */
    public function getDefault(): string
    {
        return $this->defaultLocale;
    }

    /**
     * Returns Lang loader.
     * 
     * @return LangLoaderInterface
     */
    public function getLoader(): LangLoaderInterface
    {
        if ($this->loader !== null) {
            return $this->loader;
        }

        $loaderName = Nishchay::getSetting('lang.type');
        $loader = null;
        switch ($loaderName) {
            case 'db':
                $loader = new DBLoader();
                break;
            case 'file':
            default :
                $loader = new FileLoader();
                break;
        }

        return $this->loader = $loader;
    }

}
