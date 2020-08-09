<?php

namespace Nishchay\Http\View;

use Nishchay;
use Nishchay\Exception\InvalidResponseException;
use Nishchay\Http\View\Collection as ViewCollection;
use Nishchay\Http\View\Template\TwigView;

/**
 * View locator class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Locator extends AbstractLocator
{

    /**
     * Folder name of views container.
     * 
     */
    const VIEW = 'views';

    /**
     * Initialization.
     * 
     * @param string $context
     * @param string $class
     * @param string $method
     */
    public function __construct(string $context, string $class, $method)
    {
        parent::__construct($context, $class, $method);
    }

    /**
     * Returns path to view.
     * 
     * @param string $viewName
     * @return string
     * @throws InvalidResponseException
     */
    public function getPath(string $viewName)
    {
        # First finding view based on current context.
        $view = ROOT . $this->context . DS . self::VIEW . DS . $viewName;

        if (($view = $this->getActualFile($view)) !== false) {
            return $view;
        } else {

            # Finding view by go up look up method.
            if (($view = $this->goUpLookUp($viewName)) !== false) {
                return $view;
            }

            if (($view = $this->collectionLookUp($viewName)) !== false) {
                return $view;
            }
        }

        throw new InvalidResponseException('View [' . $viewName .
                '] not found.', $this->class, $this->method, 920010);
    }

    /**
     * Go Up method to find view path.
     * 
     * @param   string      $viewName
     * @return  boolean
     */
    public function goUpLookUp(string $viewName)
    {
        $expl = explode('\\', $this->context);

        # We will loop until view found or every element has been popped out
        # from the exploded context.
        while (!empty($expl)) {
            $view = ROOT . join(DS, $expl) . DS . self::VIEW . DS . $viewName;
            if (($view = $this->getActualFile($view)) !== false) {
                return $view;
            }
            array_pop($expl);
        }
        return false;
    }

    /**
     * Finds view path by iterating over each view path of collection.
     * 
     * @param   string      $viewName
     * @return  boolean
     */
    private function collectionLookUp($viewName)
    {
        foreach (ViewCollection::get() as $parent) {
            $view = ROOT . $parent . DS . self::VIEW . DS . $viewName;

            if (($view = $this->getActualFile($view)) !== false) {
                return $view;
            }
        }
        return false;
    }

    /**
     * 
     * @param   string              $path
     * @return  boolean|string
     */
    protected function getActualFile(string $path)
    {
        foreach (Nishchay::getSupportedExtension() as $ext) {
            if (file_exists($path . TwigView::FILE_EXT . $ext)) {
                return $path . TwigView::FILE_EXT . $ext;
            }

            if (file_exists($path . '.' . $ext)) {
                return $path . '.' . $ext;
            }
        }

        return false;
    }

}
