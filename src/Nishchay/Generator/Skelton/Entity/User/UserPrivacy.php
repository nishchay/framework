<?php

namespace Nishchay\Generator\Skelton\Entity\User;

/**
 * User privacy entity class.
 *
 * #ANN_START
 * @license     https://nishchay.io/license New BSD License
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
     * User id.
     * 
     * @DataType(type=int)
     */
    public $userId;

    /**
     * Privacy name.
     * 
     * @DataType(type=string, length=100)
     */
    public $privacyName;

    /**
     * Description of this privacy.
     * 
     * @DataType(type=string)
     */
    public $description;

}
