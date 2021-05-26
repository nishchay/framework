<?php

namespace Nishchay\Attributes\Controller;

use Attribute;
use Nishchay\Attributes\AttributeTrait;

/**
 * Controller attribute class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{

    use AttributeTrait;

    const NAME = 'controller';

}
