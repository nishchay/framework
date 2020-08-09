<?php

namespace Nishchay\Generator\Skelton\Entity\User;

/**
 * User password entity class.
 *
 * #ANN_START
 * @license     http:#Nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 * #ANN_END
 * {authorName}
 * {versionNumber}
 * @Entity(name='this.base')
 */
class UserPassword
{
    /**
     * @Identity
     * @DataType(type=int,readonly=TRUE)
     */
    public $userId;
    /**
     *
     * @DataType(type=string)
     */
    public $password;
}
