<?php

namespace Nishchay\Http\View\Template;

use Twig\Source;
use Twig\Loader\LoaderInterface;
use Nishchay\Http\View\Locator;

/**
 * Class for loading twig view which extends TWIG loader interface.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class TwigViewLoader implements LoaderInterface
{

    /**
     *
     * @var Locator 
     */
    private $locator;

    /**
     *
     * @var array
     */
    private $paths = [];

    public function __construct($locator, $viewName, $path)
    {
        $this->locator = $locator;
        $this->paths[$viewName] = $path;
    }

    /**
     * Returns absolute path from given view name.
     * 
     * @param string $viewName
     * @return string
     */
    private function getPath($viewName)
    {
        if (array_key_exists($viewName, $this->paths)) {
            return $this->paths[$viewName];
        }

        return $this->paths[$viewName] = $this->locator->getPath($viewName);
    }

    /**
     * Returns true if file exists.
     * 
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return $this->getPath($name) ? true : false;
    }

    /**
     * Returns cache key.
     * 
     * @param string $name
     * @return string
     */
    public function getCacheKey(string $name): string
    {
        return $this->getPath($name);
    }

    /**
     * Returns source of view.
     * 
     * @param string $name
     * @return Source
     */
    public function getSourceContext(string $name): Source
    {
        $path = $this->getPath($name);
        return new Source(file_get_contents($path), $name, $path);
    }

    /**
     * Returns TRUE if file is modified.
     * 
     * @param string $name
     * @param int $time
     * @return boolean
     */
    public function isFresh(string $name, int $time): bool
    {
        return filemtime($this->getPath($name)) < $time;
    }

}
