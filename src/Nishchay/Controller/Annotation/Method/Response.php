<?php

namespace Nishchay\Controller\Annotation\Method;

use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Exception\InvalidAnnotationParameterException;
use Nishchay\Annotation\BaseAnnotationDefinition;

/**
 * Description of Response
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Response extends BaseAnnotationDefinition
{

    /**
     * Response type view.
     */
    const VIEW_RESPONSE = 'view';

    /**
     * Response type JSON.
     */
    const JSON_RESPONSE = 'json';

    /**
     * Response type XML.
     */
    const XML_RESPONSE = 'xml';

    /**
     * Response type null.
     */
    const NULL_RESPONSE = 'null';

    /**
     * All parameter of this annotation.
     * 
     * @var array 
     */
    private $parameter = false;

    /**
     * What kind of response of request should be.
     * 
     * @var string 
     */
    private $type = false;

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $this->parameter = $parameter;
        $this->setter($parameter, 'parameter');

        if ($this->type === false) {
            throw new InvalidAnnotationExecption('Annotation [response] requires paramter name [type].',
                    $this->class, $this->method, 914018);
        }
    }

    /**
     * Returns type of response.
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 
     * @param string $type
     */
    protected function setType($type)
    {
        if (is_string($type) && strlen($type) === 0) {
            throw new InvalidAnnotationParameterException('Response type [' . $type . ']' .
                    ' is not valid.', $this->class, $this->method, 914028);
        }
        $supported = [self::VIEW_RESPONSE, self::JSON_RESPONSE, self::XML_RESPONSE, null];

        if (!in_array(strtolower($type), $supported)) {

            throw new InvalidAnnotationParameterException('Response type [' . $type . ']' .
                    ' not supported.', $this->class, $this->method, 914019);
        }

        $this->type = ($type === null ? self::NULL_RESPONSE : $type);
    }

}
