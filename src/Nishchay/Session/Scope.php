<?php

namespace Nishchay\Session;

use Nishchay;
use Processor;
use Nishchay\Exception\ApplicationException;

/**
 * Scope Session class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Scope extends BaseSession
{

    /**
     * Current scope name.
     * 
     * @var string 
     */
    private $scopeName;

    /**
     * Initialization
     */
    public function __construct(string $scopeName = null)
    {
        parent::__construct('scope');
        $this->scopeName = $scopeName;
        $this->isInScope();
    }

    /**
     * Current Processing route have scope defined or not.
     * 
     * @return boolean
     * @throws Exception
     */
    private function isInScope()
    {

        $route = Processor::getStageDetail('object');
        $method = Nishchay::getControllerCollection()->getMethod($route->getClass() . '::' . $route->getMethod());

        # There should scope defind on route.
        if ($method->getNamedscope() === false) {
            throw new ApplicationException('Route does not have scope. '
                    . 'You should define scope for route to use scope session.', 2, null, 929010);
        }

        # We will use defualt scope if no scope has been passed.
        if ($this->scopeName === null) {
            $this->scopeName = $method->getNamedscope()->getDefault();
        }

        $scopes = $method->getNamedscope()->getName();

        # Checking if scope is from the list defined on route.
        if (in_array($this->scopeName, $scopes) === false) {
            throw new ApplicationException('Route does not belongs to scope [' . $this->scopeName . '].', 2, null, 929011);
        }

        if (!isset($this->session[$this->scopeName])) {
            $this->session[$this->scopeName] = [];
        }

        $this->session = &$this->session[$this->scopeName];
    }

}
