<?php

namespace Nishchay\Attributes;

/**
 * Description of ClassType
 *
 * @author bhavik
 */
#[\Attribute]
class ClassType
{

    use AttributeTrait;

    const NAME = 'classType';

    public function __construct(private string $type)
    {
        ;
    }

}
