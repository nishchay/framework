<?php

namespace Nishchay\Attributes\Controller\Property;

use \Nishchay\Attributes\AttributeTrait;
use Nishchay\Http\Request\Request;

/**
 * Description of Post
 *
 * @author bhavik
 */
#[\Attribute]
class Post
{

    use AttributeTrait;

    public function __construct(private ?string $name = null)
    {
        ;
    }

    public function getValue()
    {
        if ($this->name === null) {
            return Request::post();
        }

        return Request::post($this->name) ? Request::post($this->name) : null;
    }

}
