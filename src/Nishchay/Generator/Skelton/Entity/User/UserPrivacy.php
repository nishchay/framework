<?php

namespace Nishchay\Generator\Skelton\Entity\User;

/**
 * User privacy entity class.
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
class UserPrivacy
{

    /**
     * @Identity
     * @DataType(type=int, readonly=true) 
     */
    public $userPrivacyId;

    /**
     *
     * @DataType(type=int)
     */
    public $userId;

    /**
     *
     * @DataType(type=string, length=100)
     */
    public $privacyName;

    /**
     *
     * @DataType(type=string)
     */
    public $description;

}
