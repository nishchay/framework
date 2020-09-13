<?php

namespace Nishchay\Generator\Skelton\Entity\Message;

/**
 * Message entity class.
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
class Message
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $messageId;

    /**
     * On which thread message was sent to.
     * 
     * @DataType(type=int)
     */
    public $threadId;

    /**
     * Who sent message.
     * 
     * @DataType(type=int, readonly=true)
     */
    public $senderId;

    /**
     * Type of message.
     * 
     * @DataType(type=string, length=50)
     */
    public $type;

    /**
     * Content of message.
     * 
     * @DataType(type=string)
     */
    public $content;

    /**
     * Message sent at.
     * 
     * @DataType(type=datetime)
     */
    public $messageAt;

}
