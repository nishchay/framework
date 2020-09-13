<?php

namespace Nishchay\Generator\Skelton\Entity\Message;

/**
 * Thread member entity class.
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
class ThreadMember
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $threadMemberId;

    /**
     *
     * @DataType(type=int, required=true)
     */
    public $threadId;

    /**
     *
     * @DataType(type=int, required=true)
     */
    public $userId;

    /**
     *
     * @DataType(type=datetime)
     */
    public $lastSeen;

    /**
     *
     * @DataType(type=int)
     */
    public $lastReadId;

    /**
     *
     * @DataType(type=datetime)
     */
    public $lastReadAt;

    /**
     *
     * @DataType(type=int)
     */
    public $lastMessageId;

    /**
     *
     * @DataType(type=datetime)
     */
    public $lastMessageAt;

    /**
     *
     * @DataType(type=string)
     */
    public $isAdmin;

}
