<?php

namespace Nishchay\Controller\Annotation;

use Nishchay\Exception\InvalidAnnotationParameterException;
use Nishchay\Annotation\BaseAnnotationDefinition;

/**
 * Description of Only_post
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class OnlyPost extends BaseAnnotationDefinition
{

    /**
     *
     * @var array 
     */
    private $parameters;

    /**
     *
     * @var array 
     */
    private $parameter = [];

    /**
     *
     * @var boolean|string 
     */
    private $redirect = FALSE;

    /**
     * 
     * @param   string                                  $class
     * @param   string                                  $method
     * @param   array                                   $parameters
     * @throws  InvalidAnnotationParameterException
     */
    public function __construct($class, $method, $parameters)
    {
        parent::__construct($class, $method);
        $this->parameters = $parameters;

        if (!array_key_exists('parameter', $parameters)) {
            throw new InvalidAnnotationParameterException('Annotation [OnlyPost]'
                    . ' requires parameter name [parameter].', $this->class, $this->method, 914022);
        }

        $this->setter($parameters, 'parameter');
    }

    /**
     * 
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * 
     * @return array
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * 
     * @return boolean|string
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * 
     * @param   array                                    $value
     * @throws  InvalidAnnotationParameterException
     */
    protected function setParameter($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $this->parameter = $value;
    }

    /**
     * 
     * @param string $redirect
     */
    protected function setRedirect($redirect)
    {
        $this->redirect = $redirect;
    }

}
