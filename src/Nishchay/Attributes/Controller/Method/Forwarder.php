<?php

namespace Nishchay\Attributes\Controller\Method;

use Nishchay\Attributes\AttributeTrait;
use Attribute;

/**
 * Forwarder attribute class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Forwarder
{

    use AttributeTrait;

    const NAME = 'forwarder';

    public function __construct(private bool $ascent = true,
            private bool $descent = true)
    {
        ;
    }

}
