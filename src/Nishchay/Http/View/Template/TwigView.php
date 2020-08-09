<?php

namespace Nishchay\Http\View\Template;

use Nishchay;
use RuntimeException;
use Nishchay\Http\View\Locator;
use Nishchay\Http\Request\RequestStore;
use Twig\Environment;
use Nishchay\Http\View\Template\TwigViewLoader;
use Twig\TwigFunction;

/**
 * Class for rendering twig view.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class TwigView
{

    const FILE_EXT = 'twig';

    /**
     *
     * @var Locator 
     */
    private $locator;

    /**
     *
     * @var string
     */
    private $path;

    /**
     *
     * @var string 
     */
    private $viewName;

    /**
     *
     * @var Environment
     */
    private $twig;

    /**
     * Initialization.
     * 
     * @param Locator $locator
     * @param string $path
     * @param string $viewName
     */
    public function __construct(Locator $locator, string $path, string $viewName)
    {
        $this->locator = $locator;
        $this->path = $path;
        $this->viewName = $viewName;
    }

    /**
     * Renders Twig View.
     * 
     * @return null
     */
    public function render()
    {
        $this->getTwig()->registerUndefinedFunctionCallback(function ($name) {
            if (function_exists($name)) {
                return new TwigFunction($name, function () use($name) {
                    return call_user_func_array($name, func_get_args());
                });
            }
            throw new RuntimeException('Function [' . $name . '] doest not exist.', 920009);
        });
        echo $this->getTwig()->render($this->viewName, RequestStore::getAll());
    }

    /**
     * Returns Twig Environment instance.
     * 
     * @return Environment
     */
    private function getTwig(): Environment
    {
        if ($this->twig !== null) {
            return $this->twig;
        }

        $loader = new TwigViewLoader($this->locator, $this->viewName, $this->path);

        $configs = [];

        $configs['auto_reload'] = true;
        if (Nishchay::getSetting('response.templating.caching') === true) {
            $configs['cache'] = PERSISTED . 'view/';
        }

        return $this->twig = new Environment($loader, $configs);
    }

}
