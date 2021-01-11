<?php

namespace Nishchay\Generator\Skelton\Entity\Post;

/**
 * Post Category entity class.
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
class PostCategory
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $postCategoryId;

    /**
     * Post id.
     * 
     * @DataType(type=int)
     */
    public $postId;

    /**
     * Category id.
     * 
     * @DataType(type=int)
     */
    public $categoryId;

}
