<?php

namespace Nishchay\Attributes\Controller\Method;

use Nishchay;
use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Attributes\AttributeTrait;
use Attribute;

/**
 * Route placeholder attribute class.
 *
 * @license     http://www.Nishchaysource.com/license New BSD License
 * @copyright   (c) 2017, Nishchay Source
 * @author      Bhavik Patel
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Service
{

    use AttributeTrait {
        verify as parentVerify;
    }

    const NAME = 'service';

    public function __construct(private string|array $fields = 'all',
            private ?bool $token = null, private bool|array $supported = false,
            private array $always = [])
    {
        
    }

    /**
     * Sets supported field names.
     * 
     * @param   boolean|string|array    $supported
     * @return  \Nishchay\Service\Annotation\Service
     */
    public function verify()
    {
        $this->parentVerify();

        if ($this->token === null) {
            $this->token = (bool) Nishchay::getSetting('service.token.enable');
        }

        if (is_bool($this->supported)) {
            $this->supported = false;
            return;
        }

        if (is_array($this->fields)) {
            $diff = array_diff($this->fields, $this->supported);
            if (count($diff) > 0) {
                throw new InvalidAttributeException('Fields'
                                . ' [' . implode(',', $diff) . '] defined as'
                                . ' default demand should exist in'
                                . ' supported(if support parameter defined)'
                                . ' fields.', $this->class, $this->method,
                                928001);
            }
        }
    }

}
