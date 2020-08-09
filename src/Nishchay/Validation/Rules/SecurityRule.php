<?php

namespace Nishchay\Validation\Rules;

/**
 * Security related validation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class SecurityRule extends AbstractRule
{

    /**
     * Security rule name.
     */
    const NAME = 'security';

    /**
     * Returns name of validation type.
     * 
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }
}
