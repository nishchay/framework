<?php

namespace Nishchay\Generator\Skelton\Entity\Message;

/**
 * Message attachment entity class.
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
class MessageAttachment
{
    
    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $attachmentId;

    /**
     *
     * @DataType(type=int, required=true)
     */
    public $messageId;

    /**
     *
     * @DataType(type=string, required=true)
     */
    public $path;

    /**
     *
     * @DataType(type=string, length=200)
     */
    public $name;
}
