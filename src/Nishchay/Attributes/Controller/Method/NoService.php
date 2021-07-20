<?php

namespace Nishchay\Attributes\Controller\Method;

use Nishchay\Attributes\AttributeTrait;
use Attribute;

/**
 * NoService attribute class.
 *
 * @license     http://www.Nishchaysource.com/license New BSD License
 * @copyright   (c) 2017, Nishchay Source
 * @author      Bhavik Patel
 */
#[Attribute(Attribute::TARGET_METHOD)]
class NoService
{

    use AttributeTrait;

    const NAME = 'noservice';

}
