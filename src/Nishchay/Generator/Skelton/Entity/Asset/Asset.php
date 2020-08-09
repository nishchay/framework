<?php

namespace Nishchay\Generator\Skelton\Entity\Asset;

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
class Asset
{

    /**
     *
     * @Identity
     * @DataType(type=int,readonly=true)
     */
    public $assetId;

    /**
     *
     * @DataType(type=string,length=100)
     */
    public $name;

    /**
     *
     * @DataType(type=int)
     */
    public $categoryId;

    /**
     *
     * @DataType(type=int)
     */
    public $typeId;

    /**
     *
     * @DataType(type=string,length=20)
     */
    public $code;

}
