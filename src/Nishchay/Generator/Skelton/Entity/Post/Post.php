<?php

namespace Nishchay\Generator\Skelton\Entity\Post;

/**
 * Post entity class.
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
class Post
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $postId;
    
    /**
     * Type of post.
     * 
     * @DataType(type=string, length=50)
     */
    public $type;
    
    /**
     * User id of this post.
     * 
     * @DataType(type=int, required=true) 
     */
    public $userId;
    
    /**
     * Content of this post.
     * 
     * @DataType(type=string)
     */
    public $content;
    
    /**
     * List of tags in this post.
     * 
     * @DataType(type=string)
     */
    public $tags;
    
    /**
     * Privacy id.
     * 
     * @DataType(type=int)
     */
    public $privacyId;
    
    /**
     * Number of comment on this post.
     * 
     * @DataType(type=int)
     */
    public $commentCount;

}
