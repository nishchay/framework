<?php

namespace Nishchay\Controller\Annotation\Method\Parameter;

use Nishchay\Exception\InvalidAnnotationParameterException;
use Nishchay\Exception\BadRequestException;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Http\Request\Request;

/**
 * Post annotation  definition class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Post extends BaseAnnotationDefinition
{

    /**
     * Holds value of the actual request parameter. 
     * 
     * @var string|array 
     */
    private $post_value = FALSE;

    /**
     * Post request parameter name to look for.
     * 
     * @var string|array 
     */
    private $name = FALSE;

    /**
     * Default value when request parameter name missing.
     * 
     * @var boolean|string 
     */
    private $default = FALSE;

    /**
     * Redirect to url whem request name missing.
     * 
     * @var boolean|string 
     */
    private $redirect = FALSE;

    /**
     * Parameter defined in annotation.
     * 
     * @var array 
     */
    private $parameter = FALSE;

    /**
     * Value of as parameter.
     * 
     * @var string 
     */
    private $as = 'array';

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $this->setter($parameter, 'parameter');
        $this->parameter = $parameter;
        $this->post_value = $this->processRequest();
    }

    /**
     * Prcesses request to assign request parameter value to $get_value.
     * 
     * @return string|array
     * @throws InvalidAnnotationParameterException
     */
    private function processRequest()
    {
        if ($this->name === FALSE) {
            throw new InvalidAnnotationParameterException('Annotation [post] requires [name] parameter.', $this->class, $this->method, 914007);
        }

        if (is_string($this->name)) {
            $value = Request::post($this->name);
        } else {
            #Looping through all array values
            foreach ($this->name as $key) {
                $value[$key] = Request::post($key);

                if (!$value[$key]) {
                    $value[$key] = $this->requestNotFound();
                }
            }
        }


        if ($value) {
            return $value;
        } else {
            return $this->requestNotFound();
        }
    }

    /**
     * Decides which action should be taken when request paramter not found which is defined in annotation.
     * 
     */
    private function requestNotFound()
    {
        if ($this->default !== FALSE) {
            return $this->default;
        } elseif ($this->redirect !== FALSE) {
            Request::redirect($this->redirect);
        } else {
            throw new BadRequestException('Request could not be satisfied.', null, null, 914008);
        }
    }

    /**
     * Returns name value defined in annotation.
     * 
     * @return string|array
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns applicable default value in condition when request parameter is missing.
     * 
     * @return boolean
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Returns redirect value defined in annotation.
     * 
     * @return string|boolean
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Returns value of the request paramter as defiend in annotation.
     * 
     * @param type $param
     */
    public function getRequestValue()
    {
        return $this->post_value;
    }

    /**
     * Sets name value defined in annotation.
     * 
     * @param string|array $name
     */
    protected function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Sets default value of the get request parameter.
     * 
     * @param   boolean                                 $defalut
     * @throws  InvalidAnnotationParameterException
     */
    protected function setDefault($defalut)
    {
        $this->default = $defalut;
    }

    /**
     * Sets url to redirect to when request parameter missing.
     * 
     * @param   string                                  $redirect
     * @throws  InvalidAnnotationParameterException
     */
    protected function setRedirect($redirect)
    {
        if (!is_string($redirect)) {
            throw new InvalidAnnotationParameterException('Annotation [post]'
                    . ' parameter name [redirect] must be string.', $this->class, $this->method, 914009);
        }

        $this->redirect = $redirect;
    }

    /**
     * Returns type of request parameter that should be return.
     * 
     * @return string
     */
    public function getAs()
    {
        return $this->as;
    }

    /**
     * Sets type of request parameter should be return.
     * 
     * @param string $as
     */
    protected function setAs($as)
    {
        $this->as = $as;
    }

}
