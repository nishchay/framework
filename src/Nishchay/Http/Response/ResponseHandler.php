<?php

namespace Nishchay\Http\Response;

use Nishchay;
use Processor;
use Nishchay\Exception\InvalidResponseException;
use Nishchay\Service\ServicePostProcess;
use Nishchay\Http\Response\Response;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Utility\MethodInvokerTrait;
use Nishchay\Http\View\ViewHandler;

/**
 * Response class for sending view.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ResponseHandler
{

    use MethodInvokerTrait;

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
     * What to render returned from processed controller method.
     * 
     * @var srting|array 
     */
    private $render = null;

    /**
     * Response type of the processed controller method.
     * 
     * @var string 
     */
    private $response;

    /**
     * Context.
     * 
     * @var string 
     */
    private $context;

    /**
     * View files to render.
     * 
     * @var array 
     */
    private $views = [];

    /**
     *
     * @var ViewHandler
     */
    private $viewHandler;

    /**
     * 
     * @param string $class
     * @param string $method
     * @param mixed $render
     * @param string $context
     */
    public function __construct($class, $method, $render, $context = null)
    {
        $this->class = $class;
        $this->method = $method;
        $this->render = $render;
        $globalHandler = Nishchay::getHandlerCollection()->getGlobal();
        if ($globalHandler === false || $globalHandler !== $class) {
            $this->context = Processor::getStageDetail('context');

            # Getting response type of currently processed route.
            $object = Nishchay::getControllerCollection()
                    ->getMethod($class . '::' . $method);
            $this->response = strtolower($object->getResponse()->getType());
        } else {
            $this->context = $context;
            $this->response = 'null';
        }

        $this->respond();
    }

    /**
     * 
     * @return NULL
     */
    private function respond()
    {
        $this->invokeMethod([$this, 'generate' . (ucfirst($this->response) . 'Response')]);
    }

    /**
     * Do nothing.
     * 
     * @return boolean
     */
    protected function generateNullResponse()
    {
        if (is_scalar($this->render)) {
            if (strpos($this->render, 'view:') === 0) {
                $this->render = substr($this->render, 5);
                return $this->generateViewResponse();
            }
            echo $this->render;
        } else if (is_array($this->render) || is_object($this->render)) {
            return $this->generateJsonResponse();
        }
        return true;
    }

    /**
     * Generate response type JSON.
     */
    protected function generateJsonResponse()
    {
        if (!is_array($this->render)) {
            throw new InvalidResponseException('Route must return array when'
                    . ' response type is JSON.', $this->class, $this->method, 920004);
        }
        Response::setContentType('json');
        if ($this->serviceCheck()) {
            echo json_encode($this->render);
        }
    }

    /**
     * Generate response type XML.
     */
    protected function generateXmlResponse()
    {
        if (!is_array($this->render)) {
            throw new InvalidResponseException('Route must return array when'
                    . ' response type is XML.', $this->class, $this->method, 920005);
        }
        Response::setContentType('xml');
        if ($this->serviceCheck()) {
            echo ArrayUtility::toXML($this->render, false);
        }
    }

    /**
     * If the processing route is service then response will be filtered based
     * on Service attribute.
     *  
     * @return boolean
     */
    private function serviceCheck()
    {
        if (Processor::isService() === false) {
            return true;
        }

        $render = (new ServicePostProcess(Nishchay::getControllerCollection()
                        ->getMethod("{$this->class}::{$this->method}")
                        ->getService(), $this->render))
                ->check();
        if (is_array($render)) {
            $this->render = $render;
            return true;
        }
        return $render;
    }

    /**
     * Prepares response type view.
     */
    protected function generateViewResponse()
    {
        $this->render = is_string($this->render) ?
                [$this->render] : $this->render;

        if (!is_array($this->render)) {
            throw new InvalidResponseException('Route should return'
                    . ' view name.', $this->class, $this->method, 920006);
        }

        foreach ($this->render as $value) {
            if (!is_string($value)) {
                throw new InvalidResponseException('One of view name is'
                        . ' not string.', $this->class, $this->method, 920007);
            }

            $this->views[] = $value;
        }

        $this->renderView();
    }

    /**
     * Renders response type view.
     */
    private function renderView()
    {
        # Controller should return view names.
        if (empty($this->views)) {
            throw new InvalidResponseException('Method [' . $this->class . '::' . $this->method . '] did not returned'
                    . ' view name.', $this->class, $this->method, 920008);
        }

        # Now iterating over each requested view to find actual view path.
        # We will throw error if any of the view path not found.
        foreach ($this->views as $viewName) {
            if (strpos($viewName, 'view:') === 0) {
                $viewName = substr($viewName, 5);
            }
            $this->getViewHandler()->render($viewName);
        }
    }

    /**
     * Returns view handler.
     * 
     * @return ViewHandler
     */
    private function getViewHandler()
    {
        if ($this->viewHandler !== null) {
            return $this->viewHandler;
        }

        return $this->viewHandler = new ViewHandler($this->class, $this->method, $this->context);
    }

}
