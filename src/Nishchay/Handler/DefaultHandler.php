<?php

namespace Nishchay\Handler;

use Nishchay;
use Processor;
use Exception;
use Nishchay\Http\Response\Response;
use Nishchay\Console\Printer;
use Nishchay\Utility\MethodInvokerTrait;
use Nishchay\Utility\Coding;

/**
 * Default Exception Handler of the Nishchay.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DefaultHandler
{

    use MethodInvokerTrait;

    /**
     * Handles all type of exception.
     */
    public function handlerAll($e)
    {
        class_exists('Processor') && Processor::isService() ?
                        $this->generateServiceResponse($e) :
                        $this->generateViewResponse($e);
        exit;
    }

    /**
     * Generates view response.
     * 
     * @param \Nishchay\Handler\Detail $e
     */
    private function generateViewResponse(Detail $e)
    {
        # We will not display error when setting is off for the occurred error
        # type.
        if ($e->isShowable() === false) {
            return false;
        }
        try {
            if (class_exists('Processor')) {
                $route = Processor::getStageDetail('object');

                # Fetching content type of the request.
                $contentType = Nishchay::getControllerCollection()
                        ->getClass($route->getClass())
                        ->getMethod($route->getMethod())
                        ->getResponse()
                        ->getType();
                if (in_array(strtolower($contentType), ['json', 'xml'])) {
                    return $this->generateServiceResponse($e, $contentType);
                }
            }
        } catch (Exception $ex) {
            // This occurs when route does not exist.
            // Stage starts after route found.
        }
        $code = $e->getCode();
        $errorString = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $type = $e->getType();
        $trace = $e->getTrace();

        if (Nishchay::isApplicationRunningNoConsole()) {
            include_once __DIR__ . DS . 'views' . DS . 'exception.php';
            return;
        }

        $code = $code > 0 ? ('(' . $code . ') ') : '';
        Printer::write($code . $errorString . PHP_EOL . ' in file ' . $file . PHP_EOL . ' at line ' . $line, Printer::RED_COLOR);
        Printer::write(PHP_EOL);
    }

    /**
     * Generates service response.
     * 
     * @param \Nishchay\Handler\Detail $e
     */
    private function generateServiceResponse(Detail $e, $type = 'JSON')
    {
        Response::setContentType($type);
        echo Coding::encodeJSON($this->getData($e));
    }

    /**
     * Returns response data for xml or json response type.
     * 
     * @param \Nishchay\Handler\Detail $e
     * @return type
     */
    private function getData(Detail $e)
    {
        $error = [
            'error' => $e->getMessage(),
            'type' => $e->getType()
        ];
        $callback = Nishchay::getSetting('service.callback');
        if ($callback === false || $this->isCallbackExist($callback) === false) {
            return $error;
        }

        return $this->invokeMethod($callback, [$error, $e]);
    }

}
