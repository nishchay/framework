<?php

namespace Nishchay\Generator\Skelton\Entity\User;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Privacy member entity class.
 *
 * #ANN_START
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 * #ANN_END
 * {authorName}
 * {versionNumber}
 * 
 */
#[Entity(name: 'this.base')]
class PrivacyMember
{

    /**
     * Privacy member id.
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $privacyMemberId;

    /**
     * User privacy id.
     * 
     */
    #[DataType(type: 'int', required: true)]
    public $userPrivacyId;

    /**
     * User id.
     * 
     */
    #[DataType(type: 'int', required: true)]
    public $userId;

}
