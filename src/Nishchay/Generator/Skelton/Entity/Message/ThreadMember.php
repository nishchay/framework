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
    private $threadMemberId;

    /**
     *
     * @DataType(type=int, required=true)
     */
    private $threadId;

    /**
     *
     * @DataType(type=int, required=true)
     */
    private $memberId;

    /**
     *
     * @DataType(type=datetime)
     */
    private $lastSeen;

    /**
     *
     * @DataType(type=int)
     */
    private $lastReadId;

    /**
     *
     * @DataType(type=datetime)
     */
    private $lastReadAt;

    /**
     *
     * @DataType(type=int)
     */
    private $lastMessageId;

    /**
     *
     * @DataType(type=datetime)
     */
    private $lastMessageAt;

    /**
     *
     * @DataType(type=string)
     */
    private $isAdmin;

}
