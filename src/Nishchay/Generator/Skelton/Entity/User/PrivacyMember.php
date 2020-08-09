<?php

namespace Nishchay\Generator\Skelton\Entity\User;

/**
 * Privacy member entity class.
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
class PrivacyMember
{

    /**
     *
     * @Identity
     * @DataType(type=int,readonly=true)
     */
    public $privacyMemberId;

    /**
     *
     * @DataType(type=int, required=true)
     */
    public $userPrivacyId;

    /**
     *
     * @DataType(type=int, required=true)
     */
    public $userId;

}
