<?php

namespace Nishchay\Attributes\Controller\Method;

use Nishchay\Attributes\AttributeTrait;
use Nishchay\Exception\InvalidAttributeException;

/**
 * Description of Response
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
#[\Attribute]
class Response
{

    use AttributeTrait {
        verify as parentVerify;
    }

    const NAME = 'response';

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
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $parameter
     */
    public function __construct(private ?string $type,
            private ?string $view = null)
    {
        $this->view !== null ?? $this->setView();
    }

    /**
     * Returns type of response.
     * 
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * 
     * @param string $type
     */
    public function verify()
    {
        $this->parentVerify();
        
        if (empty($this->type) && $this->type !== null) {
            throw new InvalidAttributeException('Response type [' . $this->type . ']' .
                            ' is not valid.', $this->class, $this->method,
                            914028);
        }
        $supported = [self::VIEW_RESPONSE, self::JSON_RESPONSE, self::XML_RESPONSE, null];

        if (!in_array(strtolower($this->type), $supported)) {

            throw new InvalidAttributeException('Response type [' . $this->type . ']' .
                            ' not supported.', $this->class, $this->method,
                            914019);
        }

        $this->type = ($this->type === null ? self::NULL_RESPONSE : $this->type);
        return $this;
    }

    /**
     * Returns view name.
     * 
     * @return string|null
     */
    public function getView(): ?string
    {
        return $this->view;
    }

    /**
     * Sets view name.
     * 
     * @param string $view
     * @return $this
     */
    public function setView()
    {
        $this->type = self::VIEW_RESPONSE;
        return $this;
    }

}
