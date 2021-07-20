<?php

namespace Nishchay\Attributes\Controller;

use Nishchay\Attributes\AttributeTrait;
use Attribute;

/**
 * Exception handler attribute class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class ExceptionHandler
{

    use AttributeTrait;

    const NAME = 'exceptionHandler';

    public function __construct(private ?string $callback = null,
            private array $order = [])
    {
        ;
    }

}
