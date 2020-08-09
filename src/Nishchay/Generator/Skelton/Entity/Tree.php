<?php

namespace Nishchay\Generator\Skelton\Entity;

/**
 * Asset entity class.
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
class Tree
{

    /**
     * @Identity
     * @DataType(type=int, readonly=TRUE)
     */
    public $treeId;

    /**
     *
     * @DataType(type=int) 
     */
    public $parentId;

    /**
     *
     * @DataType(type=string) 
     */
    public $name;

}
