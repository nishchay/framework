<?php

namespace Nishchay\Attributes\Controller\Property;

use \Nishchay\Attributes\AttributeTrait;
use Nishchay\Http\Request\Request;

/**
 * Description of Get
 *
 * @author bhavik
 */
#[\Attribute]
class Get
{

    use AttributeTrait;

    const NAME = 'get';

    public function __construct(private ?string $name = null)
    {
        ;
    }

    public function getValue()
    {
        if ($this->name === null) {
            return Request::get();
        }

        return Request::get($this->name) ? Request::get($this->name) : null;
    }

}
