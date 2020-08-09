<?php

namespace Nishchay\Session;

use Processor;

/**
 * Context Session class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Context extends BaseSession
{

    /**
     * Current processing request's context.
     * 
     * @var string 
     */
    private $context;

    /**
     * Initialization
     */
    public function __construct()
    {
        parent::__construct('context');
        $this->setContext();
    }

    /**
     * Set's current processing request's context to this class so that 
     * it allows us to create session for current context only.
     */
    private function setContext()
    {
        $this->context = Processor::getStageDetail('context');

        if (array_key_exists($this->context, $this->session) === false) {
            $this->session[$this->context] = [];
        }

        $this->session = &$this->session[$this->context];
    }

}
