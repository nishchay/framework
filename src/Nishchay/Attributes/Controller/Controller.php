<?php

namespace Nishchay\Attributes\Controller;

use Attribute;

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

    const NAME = 'controller';

}
