<?php

namespace Nishchay\Generator\Skelton\Entity\Message;

/**
 * Message entity class.
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
class Message
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $messageId;

    /**
     *
     * @DataType(type=int)
     */
    public $threadId;

    /**
     *
     * @DataType(type=int, readonly=true)
     */
    public $senderId;

    /**
     *
     * @DataType(type=string, length=50)
     */
    public $type;

    /**
     *
     * @DataType(type=string)
     */
    public $content;

    /**
     *
     * @DataType(type=datetime)
     */
    public $messageAt;

}
