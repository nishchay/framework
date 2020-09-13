<?php

namespace Nishchay\Generator\Skelton\Entity\Message;

/**
 * Message attachment entity class.
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
class MessageAttachment
{
    
    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $attachmentId;

    /**
     *  Message id.
     * 
     * @DataType(type=int, required=true)
     */
    public $messageId;

    /**
     * Path to attachment.
     * 
     * @DataType(type=string, required=true)
     */
    public $path;

    /**
     * Name of attachment.
     * 
     * @DataType(type=string, length=200)
     */
    public $name;
}
