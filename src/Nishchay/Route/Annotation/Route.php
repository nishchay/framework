<?php

namespace Nishchay\Route\Annotation;

use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Exception\InvalidAnnotationParameterException;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Controller\Annotation\Controller;

/**
 * Route annotation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Route extends BaseAnnotationDefinition
{

    /**
     * All valid request methods.
     * 
     * @var array 
     */
    private static $requestMethods = ['GET', 'POST', 'PUT', 'DELETE',
        'OPTIONS', 'HEAD', 'TRACE', 'CONNECT'];

    /**
     * File name where the route is located.
     * 
     * @var string 
     */
    private $file;

    /**
     * Prefix parameter value.
     * Whether to ignore routing prefix.
     * 
     * @var boolean 
     */
    private $prefix = true;

    /**
     * Defined parameters.
     * 
     * @var array 
     */
    private $parameter = [];

    /**
     * Path parameter value.
     * Path of the route.
     * 
     * @var string|boolean 
     */
    private $path = false;

    /**
     * See parameter value.
     * Whether to take method name as route name.
     * 
     * @var boolean 
     */
    private $see = false;

    /**
     * Incoming parameter.
     * Allows or disallows incoming request on route.
     * 
     * @var boolean 
     */
    private $incoming = true;

    /**
     * Special values within route.
     * 
     * @var array 
     */
    private $placeholder = [];

    /**
     * Whether special annotation defined on method.
     * @var boolean 
     */
    private $placeholderAnnotation = false;

    /**
     * Type parameter value.
     * Type of request.
     * 
     * @var string|array 
     */
    private $type = false;

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $parameter
     * @param   boolean     $special
     */
    public function __construct($class, $method, $parameter, Controller $controller, $special)
    {
        parent::__construct($class, $method);
        $this->parameter = ArrayUtility::customeKeySort($parameter, ['see', 'route']);

        # Special annotation defined on same method.
        $this->placeholderAnnotation = $special;
        $this->setter($this->parameter, 'parameter');

        # Now we should verify that defintion have set path.
        # We also have to preg quote the path.
        $this->verify($controller);
    }

    /**
     *  Return file name of controller class of this annotation.
     * 
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Returns ignore parameter value.
     * 
     * @return boolean
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Returns parameters.
     * 
     * @return array
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Returns path.
     * 
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns see parameter.
     * 
     * @return string
     */
    public function getSee()
    {
        return $this->see;
    }

    /**
     * Returns type parameter value.
     * Type of request to be supported by this route.
     * 
     * @return string|array
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 
     * @param boolean $prefix
     */
    protected function setPrefix($prefix)
    {
        $this->prefix = (bool) $prefix;
    }

    /**
     * Process ending verification.
     * Prefixes route if the routing is defiend on controller
     * and 
     * 
     * @throws InvalidAnnotationExecption
     */
    private function verify(Controller $controller)
    {
        if ($this->path === false) {
            throw new InvalidAnnotationExecption('Annotation [route] requires'
                    . ' at least one of [path] or [see] paraemter.', $this->class, $this->method, 926005);
        }

        # We wiil prefix annotation if controler class has @routing annotaiton
        # and prefix parameter value is TRUE.
        # When prefix is FALSE, we ignore prefixing of route.
        if ($controller->getRouting() !== false && $this->prefix === true) {
            $this->path = $controller->getRouting()->getPrefix() . '/' . $this->path;
        }

        $this->path = trim($this->path, '/');

        if (empty($this->path)) {
            throw new InvalidAnnotationExecption('Annotation [route] paramter'
                    . ' name [path] should not be empty.', $this->class, $this->method, 926006);
        }

        # We here now preg quoting path except curly bracket start & end and 
        # double question mark.
        # We will replace this with their regualr expression while storing into
        # collection.
        $this->path = str_replace(['\?', '\{', '\}'], ['?', '{', '}'], preg_quote($this->path));
    }

    /**
     * Sets path parameter value.
     * Path value is ignore and replaced by method name if value of see 
     * parameter is TRUE.
     * 
     * @param string $path
     */
    protected function setPath($path)
    {
        # We will pick method name as route path when see is TRUE.
        $this->path = $this->see === true ? $this->method : $path;

        # Path must be string.
        if (!is_string($this->path)) {
            throw new InvalidAnnotationParameterException('Annotation [route] paramter name [path] ' .
                    ' should be string.', $this->class, $this->method, 926007);
        }

        # Let's find if there any sepecial segment in route path.
        preg_match_all('#(\{)+(\w+)+(\})#', $this->path, $match);
        $this->placeholder = $match[2];

        # When we finds special segment inside path, we must need special
        # annotation defined on same method.
        if (count($this->placeholder) > 0 && $this->placeholderAnnotation === false) {
            throw new InvalidAnnotationExecption('Placeholder value in route requires '
                    . '[placeholder] annotation.', $this->class, $this->method, 926008);
        }
    }

    /**
     * Sets see parameter value.
     * This sets path by taking method name if see is TRUE.
     * 
     * @param boolean $see
     */
    protected function setSee($see)
    {
        if (!is_bool($see)) {
            throw new InvalidAnnotationParameterException('Annotation [route]'
                    . ' parameter name [see] must be boolean.', $this->class, $this->method, 926009);
        }

        $this->see = $see;

        if ($see === true) {
            $this->path = $this->method;
        }
    }

    /**
     * Returns incoming parameter value.
     * 
     * @return type
     */
    public function getIncoming()
    {
        return $this->incoming;
    }

    /**
     * Set incoming parameter value.
     * 
     * @param boolean $incoming
     */
    protected function setIncoming($incoming)
    {
        $this->incoming = $incoming;
    }

    /**
     * Returns special values defined inside route.
     * 
     * @return array
     */
    public function getSpecialValues()
    {
        return $this->placeholder;
    }

    /**
     * Sets type parameter value.
     * Sets which type of request should be handled by this route.
     * Can be GET, POST and/or etc.
     * 
     * @param string|array $type
     */
    protected function setType($type)
    {
        $type = (array) $type;

        $this->type = array_map(function($value) {
            return strtoupper($value);
        }, $type);
    }

    /**
     * Returns all valid request methods.
     * 
     */
    public function getValidRequestMethods()
    {
        return self::$requestMethods;
    }

}
