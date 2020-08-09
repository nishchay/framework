<?php

namespace Nishchay\Generator\Skelton\Entity\Post;

/**
 * Post entity class.
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
class Post
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $postId;
    
    /**
     *
     * @DataType(type=string, length=50)
     */
    public $type;
    
    /**
     *
     * @DataType(type=int, required=true) 
     */
    public $userId;
    
    /**
     *
     * @DataType(type=string)
     */
    public $content;
    
    /**
     *
     * @DataType(type=string)
     */
    public $tags;
    
    /**
     *
     * @DataType(type=int)
     */
    public $privacyId;
    
    /**
     *
     * @DataType(type=int)
     */
    public $commentCount;

}
