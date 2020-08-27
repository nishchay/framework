<?php

namespace Nishchay\Http\View;

use Nishchay;
use ReflectionClass;
use Nishchay\Http\View\Locator;
use Nishchay\Http\Render;
use Nishchay\Http\View\Template\TwigView;

/**
 * View handler class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ViewHandler
{

    /**
     * Processed controller class name.
     * 
     * @var string 
     */
    private $class = null;

    /**
     * Processed controller method name.
     * 
     * @var string 
     */
    private $method = null;

    /**
     * Context.
     * 
     * @var string 
     */
    private $context;

    /**
     * View Locator instance.
     * 
     * @var \Nishchay\Http\View\Locator 
     */
    private $locator;

    /**
     * 
     * @param type $class
     * @param type $method
     * @param type $context
     */
    public function __construct(string $class, $method, string $context)
    {
        $this->class = $class;
        $this->method = $method;
        $this->context = $context;
    }

    /**
     * Returns view locator.
     * 
     * @return \Nishchay\Http\View\Locator
     */
    private function getViewLocator()
    {
        if ($this->locator !== null) {
            $this->locator->setContext($this->context)
                    ->setClass($this->class)
                    ->setMethod($this->method);
            return $this->locator;
        }

        $locator = Nishchay::getSetting('response.locator');
        $reflection = new ReflectionClass(
                $locator !== null ? ('Extension\\' . $locator) : Locator::class
        );
        return $this->locator = $reflection->newInstanceArgs(
                [
                    $this->context,
                    $this->class,
                    $this->method
                ]
        );
    }

    /**
     * Renders response type view.
     */
    public function render($viewName)
    {
        $this->handleView($viewName);
    }

    /**
     * Handles view.
     * 
     * @param type $viewName
     */
    private function handleView(string $viewName)
    {
        $viewName = $this->getViewLocator()->getPath($viewName);
        $templating = Nishchay::getSetting('response.templating.engine');

        if (is_string($templating)) {
            try {
                return Coding::invokeMethod(
                                'Extension\\' . $templating, [
                            $viewName,
                            RequestStore::getAll(),
                            $this->getViewLocator()
                                ]
                );
            } catch (ApplicationException $e) {
                throw new ApplicationException('Invalid templating'
                        . ' callback: ' . $e->getMessage());
            }
        }

        if (strpos($viewName, 'twig') !== false) {
            return $this->processTwigView($viewName, $viewName);
        }

        Render::view($viewName);
    }

    /**
     * Process twig view.
     * 
     * @param string $path
     * @param string $viewName
     * @return string
     */
    private function processTwigView(string $path, string $viewName)
    {
        return $this->getTwigView($path, $viewName)->render();
    }

    /**
     * Returns TwigView Handler.
     * 
     * @param string $path
     * @param string $viewName
     * @return TwigView
     */
    private function getTwigView(string $path, string $viewName)
    {
        return new TwigView($this->getViewLocator(), $path, $viewName);
    }

}
