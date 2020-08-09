<?php

namespace Nishchay\Generator\Skelton\Entity\Post;

/**
 * Comment entity class.
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
class Comment
{

    /**
     *
     * @Identity
     * @DataType(type=int, required=true)
     */
    public $commentId;

    /**
     *
     * @DataType(type=int, required=true)
     */
    public $userId;

    /**
     *
     * @DataType(type=string, length=50)
     */
    public $type;

    /**
     *
     * @DataType(type=int)
     */
    public $typeId;

    /**
     *
     * @DataType(type=string)
     */
    public $content;

}
