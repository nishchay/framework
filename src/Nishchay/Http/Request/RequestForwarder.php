<?php

namespace Nishchay\Http\Request;

use Nishchay\Exception\RequestMethodNotAllowedException;

/**
 * Request Forwarder class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class RequestForwarder
{

    /**
     * Next route.
     * 
     * @var string 
     */
    private $route = '';

    /**
     * Request type.
     * 
     * @var type 
     */
    private $type;

    /**
     * Whether to flush to request store after forwarding request.
     * 
     * @var boolean 
     */
    private $flushRequestStore = FALSE;

    /**
     * GET parameter.
     * 
     * @var array 
     */
    private $getParameter = array();

    /**
     * POST parameter.
     * 
     * @var array 
     */
    private $postParameter = array();

    /**
     * 
     * @param string $route
     */
    public function __construct($route, $type = 'GET')
    {
        $this->route = (string) $route;
        $this->type = strtoupper((string) $type);
        $this->validate();
    }

    /**
     * Validates forwarding request.
     */
    private function validate()
    {
        if (!in_array($this->type, ['GET', 'POST', 'PUT', 'DELETE',
                    'OPTIONS', 'HEAD', 'TRACE', 'CONNECT'])) {
            throw new RequestMethodNotAllowedException('Request cannot '
                    . 'be forwarded with method type [' . $this->type . '].', null, null, 920001);
        }
    }

    /**
     * Enable request store to be flush while forwarding request.
     * 
     * @return \Nishchay\Http\RequestForwarder
     */
    public function withFlushRequestStore()
    {
        $this->flushRequestStore = TRUE;
        return $this;
    }

    /**
     * Sets GET parameter for request.
     * 
     * @param   array       $parameter
     * @return \Nishchay\Http\RequestForwarder
     */
    public function withGetParameter($parameter)
    {
        $this->getParameter = $parameter;
        return $this;
    }

    /**
     * Set POST parameter for request.
     * 
     * @param   array       $parameter
     * @return \Nishchay\Http\RequestForwarder
     */
    public function withPostParameter($parameter)
    {
        $this->postParameter = $parameter;
        return $this;
    }

    /**
     * Returns route where request  should be forwarded to.
     * 
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Returns TRUE  if request store to be flush before forwarding request.
     * 
     * @return boolean
     */
    public function isForwardingWithFlushRequestStore()
    {
        return $this->flushRequestStore;
    }

    /**
     * Returns get parameter to be passed to next forwarding request.
     * 
     * NOTE:
     * This method being used in Forwarder class with dynamic name.
     * 
     * @return array
     */
    public function getForwardingGetParameter()
    {
        return $this->getParameter;
    }

    /**
     * Returns post parameter to be passed to next forwarding  request.
     * 
     * NOTE : 
     * This method being used in Forwarder class with dynamic name.
     * 
     * @return array
     */
    public function getForwardingPostParameter()
    {
        return $this->postParameter;
    }

    /**
     * Returns request method type.
     * 
     * @return string
     */
    public function getForwardingTypeParameter()
    {
        return $this->type;
    }

}
