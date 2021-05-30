<?php

namespace Nishchay\Generator\Skelton\Entity\Post;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Attachment entity class.
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
class PostAttachment
{

    /**
     * Attachment id.
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $attachmentId;

    /**
     * Post id.
     * 
     */
    #[DataType(type: 'int', required: true)]
    public $postId;

    /**
     * Path to attachment.
     * 
     */
    #[DataType(type: 'string', required: true)]
    public $path;

    /**
     * Name of attachment.
     * 
     */
    #[DataType(type: 'string', length: 200)]
    public $name;

}
