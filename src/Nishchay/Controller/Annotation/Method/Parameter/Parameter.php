<?php

namespace Nishchay\Controller\Annotation\Method\Parameter;

use Nishchay;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Exception\InvalidAnnotationParameterException;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Controller\Annotation\Method\Parameter\Get;
use Nishchay\Controller\Annotation\Method\Parameter\Post;
use Nishchay\Controller\Annotation\Method\Parameter\Segment;

/**
 * Processes controller method's parameter to autobind value.
 * Every parameter of the method instanciate this class to process and find value depends on annotaiton defined.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Parameter extends BaseAnnotationDefinition
{

    /**
     * Holds GET request paramter value on of which is defiend in annotation.
     * 
     * @var string|array 
     */
    private $get;

    /**
     * Holds POST request paramter value on of which is defiend in annotation.
     * 
     * @var string|array 
     */
    private $post;

    /**
     * Request parameter value as defiend get or post annotation.
     * 
     * @var string|array 
     */
    private $annotationValue = FALSE;

    /**
     * Doc annotation value.
     * 
     * @var array 
     */
    private $doc;

    /**
     * Segmetn value.
     * 
     * @var string 
     */
    private $segment;

    /**
     * Paramter name for which this class instanciated.
     * 
     * @var string 
     */
    private $parameterName;

    /**
     * Annotaiton name which is defined in method paramter default value.
     * 
     * @var string 
     */
    private $annotationName;

    /**
     * 
     * @param   string                  $class
     * @param   string                  $method
     * @param   array                   $annotation
     * @param   string                  $parameterName
     * @throws  NotSupportedException
     */
    public function __construct($class, $method, $annotation, $parameterName)
    {
        parent::__construct($class, $method);
        $this->parameterName = $parameterName;

        if (count($annotation) > 1) {
            throw new InvalidAnnotationExecption('Method parameter does not support'
                    . ' more than one annotation.', $this->class, $this->method, 914004);
        }

        $this->setter($annotation);
    }

    /**
     * Returns get request paramter values which is(are) defined in get annotation.
     * 
     * @return string|array
     */
    public function getGet()
    {
        return $this->get;
    }

    /**
     * Returns post request paramter values which is(are) defined in post annotation.
     * 
     * @return string|array
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Returns value of annotation asociated doc annotation.
     * Values of this annotaiton depends doc annotation defined on method.
     * 
     * @return string|array
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * Returns segment value of the request.
     * Value can be retreived using index or key.
     * 
     * @return type
     */
    public function getSegment()
    {
        return $this->segment;
    }

    /**
     * Processes and sets get request paramter value which are defined.
     * 
     * @param   string|array   $parameter
     */
    protected function setGet($parameter)
    {
        $this->annotationName = 'get';
        $this->get = new Get($this->class, $this->method, $parameter);
        $this->annotationValue = $this->get->getRequestValue();
    }

    /**
     * Prcesses and sets post request parameter value which are defined.
     * 
     * @param type $parameter
     */
    protected function setPost($parameter)
    {
        $this->annotationName = 'post';
        $this->post = new Post($this->class, $this->method, $parameter);
        $this->annotationValue = $this->post->getRequestValue();
    }

    /**
     * Processes defined annotation along with relative annotattion on method.
     * 
     * @throws  InvalidAnnotationParameterException
     */
    protected function setDoc()
    {
        #@doc actual definition is defined on method. We are fetching method annoation object then 
        #using the same we will get doc annotation defined for this paramter name.
        $method = Nishchay::getControllerCollection()->getClass($this->class)->getMethod($this->method);

        if ($method->getDoc($this->parameterName) === FALSE) {
            throw new InvalidAnnotationExecption('It seems @doc_' . $this->parameterName .
                    ' not defined on method.', $this->class, $this->method, 914005);
        }

        $this->doc = $method->getDoc($this->parameterName);
        $this->annotationName = $this->doc->getAnnotation();
        $method_name = 'set' . ucfirst($this->annotationName);

        if (!method_exists($this, $method_name)) {
            throw new InvalidAnnotationParameterException('Annotation defined'
                    . ' in parameter name [annotaiton] of [doc] annotation is'
                    . ' not supported.', $this->class, $this->method, 914006);
        }

        call_user_func([$this, $method_name], $this->doc->getParameter());
    }

    /**
     * Returns value of segment by index or key.
     * 
     * @param   array                                   $parameter
     * @throws  InvalidAnnotationParameterException
     */
    protected function setSegment($parameter)
    {
        $this->annotationName = 'segment';
        $this->segment = new Segment($this->class, $this->method, $parameter);
        $this->annotationValue = $this->segment->getSegmentValue();
    }

    /**
     * Returns request paramter value as defined in get or post annotation.
     * 
     * @return string|array
     */
    public function getAnnotationValue()
    {
        return $this->annotationValue;
    }

    /**
     * Returns name of the annotation.
     * 
     * @return string
     */
    public function getAnnotationName()
    {
        return $this->annotationName;
    }

}
