<?php

namespace Nishchay\Generator\Skelton\Entity\Message;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

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
 * 
 */
#[Entity(name: 'this.base')]
class Message
{

    /**
     * Message id
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $messageId;

    /**
     * On which thread message was sent to.
     * 
     */
    #[DataType(type: 'int')]
    public $threadId;

    /**
     * Who sent message.
     * 
     */
    #[DataType(type: 'int', readOnly: true)]
    public $senderId;

    /**
     * Type of message.
     * 
     */
    #[DataType(type: 'string', length: 50)]
    public $type;

    /**
     * Content of message.
     * 
     */
    #[DataType(type: 'string')]
    public $content;

    /**
     * Message sent at.
     * 
     */
    #[DataType(type: 'datetime')]
    public $messageAt;

}
