<?php

namespace Nishchay\Generator\Skelton\Entity\Message;

/**
 * Thread member entity class.
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
class ThreadMember
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $threadMemberId;

    /**
     * Thread id to which member is belongs to.
     * 
     * @DataType(type=int, required=true)
     */
    public $threadId;

    /**
     * User id.
     * 
     * @DataType(type=int, required=true)
     */
    public $userId;

    /**
     * Last visit to this thread by user.
     * 
     * @DataType(type=datetime)
     */
    public $lastSeen;

    /**
     * Last message read by this user.
     * 
     * @DataType(type=int)
     */
    public $lastReadId;

    /**
     * Last message read at.
     * 
     * @DataType(type=datetime)
     */
    public $lastReadAt;

    /**
     * Last message id which was sent by this user.
     * 
     * @DataType(type=int)
     */
    public $lastMessageId;

    /**
     * Time when last message was sent by this user.
     * 
     * @DataType(type=datetime)
     */
    public $lastMessageAt;

    /**
     * Is this user admin of thread.
     * 
     * @DataType(type=string)
     */
    public $isAdmin;

}
