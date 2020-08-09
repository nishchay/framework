<?php

namespace Nishchay\Service\Annotation;

use Nishchay;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Utility\ArrayUtility;

/**
 * Web service annotation class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Service extends BaseAnnotationDefinition
{

    /**
     * FALSE to not to support fields to request for need.
     * if no field GET parameter passed then
     * 'all' will allow returning all fields by default.
     * NULL will throw exception informing you should ask which fields
     * you want in response.
     * field name array to return list of mentioned field names.
     * 
     * @var type 
     */
    private $fields = 'all';

    /**
     * Whether token required to access web service.
     * 
     * @var boolean 
     */
    private $token = true;

    /**
     *
     * @var type 
     */
    private $supported = false;

    /**
     * Service always respond with following fields.
     * 
     * @var array 
     */
    private $always = [];

    /**
     * 
     * @param type $parameter
     * @param type $class
     * @param type $method
     */
    public function __construct($parameter, $class, $method)
    {
        parent::__construct($class, $method);
        $this->token = (bool) Nishchay::getSetting('service.token.enable');
        $parameter = ArrayUtility::customeKeySort(is_array($parameter) ? $parameter : [], ['fields', 'supported']);
        $this->setter($parameter, 'parameter');
    }

    /**
     * Returns field value.
     * 
     * @return string
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns field value.
     * 
     * @param boolean|string|array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * Returns TRUE if token is required.
     * 
     * @return boolean
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Sets token TRUE or FALSE.
     * 
     * @param boolean $token
     */
    public function setToken($token)
    {
        $this->token = (bool) $token;
    }

    /**
     * Returns supported field names.
     * 
     * @return array
     */
    public function getSupported()
    {
        return $this->supported;
    }

    /**
     * Sets supported field names.
     * 
     * @param   boolean|string|array    $supported
     * @return  \Nishchay\Service\Annotation\Service
     */
    public function setSupported($supported)
    {
        $this->supported = (array) $supported;
        if (is_array($this->fields)) {
            $diff = array_diff($this->fields, $this->supported);
            if (count($diff) > 0) {
                throw new InvalidAnnotationExecption('Fields'
                        . ' [' . implode(',', $diff) . '] defined as'
                        . ' default demand should exist in'
                        . ' supported(if support parameter defined)'
                        . ' fields.', $this->class, $this->method, 928001);
            }
        }
    }

    /**
     * Returns fields to be returned always.
     * 
     * @return array
     */
    public function getAlways()
    {
        return $this->always;
    }

    /**
     * Sets fields to be returned always.
     * 
     * @param type $always
     * @return $this
     */
    public function setAlways($always)
    {
        $this->always = (array) $always;
    }

}
