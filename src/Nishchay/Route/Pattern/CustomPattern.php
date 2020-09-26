<?php

namespace Nishchay\Route\Pattern;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * Route pattern collection.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.1
 * @author      Bhavik Patel
 */
class CustomPattern extends AbstractPattern
{

    use MethodInvokerTrait;

    /**
     *
     * @var \Closure
     */
    private $processCallback;

    public function __construct($name)
    {
        parent::__construct($name);
        $this->init($name);
    }

    private function init($name)
    {
        $patterns = Nishchay::getSetting('routes.patterns');
        if (!isset($patterns->{$name})) {
            throw new ApplicationException('Route pattern [' . $name . '] does not exists.', null, null, 926018);
        }

        $pattern = $patterns->{$name};
        $this->setRoute($pattern->route ?? null)
                ->setNamedscope($pattern->namedscope ?? null)
                ->setService($pattern->service ?? null)
                ->setResponse($pattern->response ?? null);

        if (!isset($pattern->processor) || ($pattern->processor instanceof \Closure) === false) {
            throw new ApplicationException('Route pattern processor must be closure for pattern [' . $name . '].', null, null, 926019);
        }
        
        $this->processCallback = $pattern->processor;
    }

    public function processMethod(string $class, string $method)
    {
        return $this->invokeMethod($this->processCallback, [$class, $method]);
    }

}
