<?php

namespace Nishchay\Handler;

use Nishchay;
use Processor;
use Exception;
use Nishchay\Http\Response\ResponseHandler;
use Nishchay\Utility\StringUtility;
use Nishchay\Http\Response\Response;
use Nishchay\Processor\Names;
use Nishchay\Utility\MethodInvokerTrait;
use Nishchay\DI\DI;

/**
 * Exception handler dispatcher class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Dispatcher
{

    use MethodInvokerTrait;

    /**
     * Exception class name.
     * Name exception which has been thrown.
     * 
     * @var string 
     */
    private $exceptionClass;

    /**
     * Handles exception.
     * 
     * @param   \Nishchay\Handler\Detail    $detail
     */
    public function handle(Detail $detail)
    {
        $this->setStatusCode($detail);
        $this->setExceptionClass($this->getBaseClassName($detail->getType()));

        # Error occurred before start of an application.
        if (class_exists('Processor', false) === false) {
            return $this->getResponse(new DefaultHandler(), $detail);
        }

        $handlerClass = $this->getHandler();

        # In the case of exception handler defined on controller or route,
        # handlerClass is instnace of controller class.
        $instance = is_object($handlerClass) === false ? (new $handlerClass) : $handlerClass;
        $response = $this->getResponse($instance, $detail);

        if ($this->respondIfGlobal($handlerClass, $response, $detail) !== false) {
            return false;
        }

        # In case of user defined handler, we will respond with response 
        # returned by handler and hand it over to ResponseHandler so it can
        # generate request response based on route response type.
        if ($handlerClass !== DefaultHandler::class) {

            # If exception handler does not returns expected response,
            # we will handle it by default handler.
            try {
                $route = Processor::getStageDetail('object');
                new ResponseHandler($route->getClass(), $route->getMethod(),
                        $response, Processor::getStageDetail('context'));
                exit;
            } catch (Exception $e) {
                return $this->getResponse(new DefaultHandler(),
                                $this->getReDetail($e, $detail));
            }
        }
    }

    /**
     * Responds if handler is global.
     * 
     * @param array $handlerClass
     * @param mixed $response
     * @param \Nishchay\Handler\Detail $detail
     * @return boolean
     */
    private function respondIfGlobal($handlerClass, $response, Detail $detail)
    {
        $globalHandler = Nishchay::getHandlerCollection()->getGlobal();
        if ($globalHandler !== false && $handlerClass === $globalHandler) {
            try {
                new ResponseHandler($handlerClass, null, $response, '');
                return true;
            } catch (Exception $e) {
                return $this->getResponse(new DefaultHandler(),
                                $this->getReDetail($e, $detail));
            }
        }

        return false;
    }

    /**
     * Returns Detail instance.
     * This is required when handler does not respond with proper response type.
     * 
     * @param Exception $e
     * @param \Nishchay\Handler\Detail $detail
     * @return \Nishchay\Handler\Detail
     */
    private function getReDetail(Exception $e, Detail $detail)
    {
        $message = "Actual: {$detail->getMessage()} Further: {$e->getMessage()}";
        return new Detail($e->getCode(), $message, $e->getFile(), $e->getLine(),
                'error', $e->getTrace());
    }

    /**
     * Sets HTTP response status code.
     * 
     * @param \Nishchay\Handler\Detail $e
     */
    private function setStatusCode(Detail $e)
    {
        $code = 500;
        if (defined($e->getActualType() . '::STATUS_CODE')) {
            $code = constant($e->getActualType() . '::STATUS_CODE');
        }

        Response::setStatus($code);
    }

    /**
     * Callas handler method and returns response returned by it.
     * 
     * @param   object      $handler
     * @return  mixed
     */
    private function getResponse($handler, $e)
    {
        $method = $this->getMethodName($handler);
        if ($method == false) {
            $handler = new DefaultHandler;
            $method = 'handlerAll';
        }

        return $this->invokeMethod([$handler, $method], [$e]);
    }

    /**
     * Returns method name which actually exist in handler class.
     * This will returns FALSE if no valid callable method found in class.
     * 
     * @param   object      $handler
     * @return  string
     */
    private function getMethodName($handler)
    {
        return method_exists($handler, $this->exceptionClass) ? $this->exceptionClass : (
                method_exists($handler, 'handlerAll') ? 'handlerAll' : false);
    }

    /**
     * Returns handler class name.
     * 
     * @return  string
     */
    private function getHandler()
    {
        $handler = false;
        try {

            # Fetching method attribute so we can find exception handler 
            # attribute defined on processing route.
            $route = Processor::getStageDetail('object');
            $class = Nishchay::getControllerCollection()
                    ->getClass($route->getClass());

            $method = $class->getMethod($route->getMethod());

            # Exception handler defined on controller class or method.
            # First we check for method if it does not exists ther will
            # find for controller class.
            if (($exceptionHandler = $method->getExceptionhandler()) === null) {
                $exceptionHandler = $class->getExceptionhandler();
            }

            # There can be no callback method but order can exists. So we will
            # find handler in defined order on route.
            if ($exceptionHandler === null || $exceptionHandler->getCallback() === null) {
                $handler = $this->getHandlerClass($method, $exceptionHandler);
            } else {
                $DI = new DI();
                $instance = $DI->create($class->getClass(), [], true);
                $callback = [$instance, $exceptionHandler->getCallback()];

                # Checking that callback method exists
                if ($this->isCallbackExist($callback)) {
                    # Replacing thrown exception class name to callback 
                    # method name so that this will be called as handler.
                    $handler = $instance;
                    $this->setExceptionClass($exceptionHandler->getCallback());
                }
            }
        } catch (Exception $e) {
            # Just preventing another exception.
            # This occurs if exception thrown before first stage of request.
            # In that there's no stage started hence we won't be able to get
            # state detail which throws another exception.
        }

        if ($handler === false) {
            $handler = $this->getHandlerClassOfGlobal();
        }

        return $handler !== false ? $handler : DefaultHandler::class;
    }

    /**
     * 
     * @param   \Nishchay\Controller\ControllerMethod    $method
     * @param   \Nishchay\Attributes\Controller\ExceptionHandler $exceptionHandler
     * @return  boolean
     */
    private function getHandlerClass($method, $exceptionHandler)
    {
        $handler = false;
        foreach ($this->getOrder($exceptionHandler) as $handlerType) {
            $handler = $this->getHandlerOf($handlerType, $method);
            if ($handler !== false) {
                return $handler;
            }
        }
        return $handler;
    }

    /**
     * Returns Handler of each type.
     * 
     * @param string $type
     * @param string $method
     * @return instanceF
     */
    private function getHandlerOf($type, $method)
    {
        if ($type === Names::TYPE_SCOPE) {
            return $this->getHandlerClassOfScope($method);
        } else if ($type === Names::TYPE_CONTEXT) {
            return $this->getHandlerClassOfContext();
        }

        return $this->getHandlerClassOfGlobal();
    }

    /**
     * Returns handler class for the scope.
     * 
     * @param \Nishchay\Controller\ControllerMethod $method
     * @return string|boolean
     */
    private function getHandlerClassOfScope($method)
    {
        # Route has not defined scope so will return false instead of class.
        if (!$method->getNamedScope()) {
            return false;
        }
        $handlerCollection = Nishchay::getHandlerCollection();
        foreach ($method->getNamedScope()->getName() as $name) {
            if (($class = $handlerCollection->get(Names::TYPE_SCOPE, $name)) !== false) {
                return $class;
            }
        }
        return false;
    }

    /**
     * Returns handler class from handler collection of context.
     * 
     * @return string|boolean
     */
    private function getHandlerClassOfContext()
    {
        $context = Processor::getStageDetail(Names::TYPE_CONTEXT);
        return Nishchay::getHandlerCollection()
                        ->get(Names::TYPE_CONTEXT, $context);
    }

    /**
     * Returns handler class from handler collection of global.
     * 
     * @return string|boolean
     */
    private function getHandlerClassOfGlobal()
    {
        return Nishchay::getHandlerCollection()
                        ->getGlobal();
    }

    /**
     * Returns order in which handler should be searched.
     * 
     * @param Nishchay\Attributes\Controller\ExceptionHandler   $exceptionHandler
     * @return array
     */
    private function getOrder($exceptionHandler)
    {
        if ($exceptionHandler === null ||
                empty($exceptionHandler->getOrder())) {
            return [Names::TYPE_SCOPE, Names::TYPE_CONTEXT, Names::TYPE_GLOBAL];
        }
        return $exceptionHandler->getOrder();
    }

    /**
     * Sets exception class.
     * 
     * @param array $class
     */
    private function setExceptionClass($class)
    {
        $class[0] = strtolower($class[0]);
        $this->exceptionClass = $class;
    }

    /**
     * Returns base class name.
     * 
     * @param   string      $class
     * @return  string
     */
    private function getBaseClassName($class)
    {
        return StringUtility::getExplodeLast('\\', $class);
    }

}
