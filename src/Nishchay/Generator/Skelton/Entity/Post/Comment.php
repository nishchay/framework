<?php

namespace Nishchay\Generator\Skelton\Entity\Post;

/**
 * Comment entity class.
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
class Comment
{

    /**
     *
     * @Identity
     * @DataType(type=int, required=true)
     */
    public $commentId;

    /**
     * User id.
     * 
     * @DataType(type=int, required=true)
     */
    public $userId;

    /**
     * On which this comment was added.
     * 
     * @DataType(type=string, length=50)
     */
    public $type;

    /**
     * Type id.
     * 
     * @DataType(type=int)
     */
    public $typeId;

    /**
     * Content of comment.
     * 
     * @DataType(type=string)
     */
    public $content;

}
