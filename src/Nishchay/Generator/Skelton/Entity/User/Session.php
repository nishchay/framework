<?php

namespace Nishchay\Generator\Skelton\Entity\User;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Session entity class.
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
class Session
{

    /**
     * Session identity id
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $sessionIdentityId;

    /**
     * Session id.
     * 
     */
    #[DataType(type: 'int', length: 200, required: true)]
    public $sessionId;

    /**
     * Data of session.
     * 
     */
    #[DataType(type: 'string')]
    public $data;

    /**
     * Time when last accessed this session.
     * 
     */
    #[DataType(type: 'datetime')]
    public $accessAt;

}
