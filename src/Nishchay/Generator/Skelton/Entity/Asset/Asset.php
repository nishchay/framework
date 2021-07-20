<?php

namespace Nishchay\Generator\Skelton\Entity\Asset;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Asset entity class.
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
class Asset
{

    /**
     *
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $assetId;

    /**
     * Name of asset.
     * 
     */
    #[DataType(type: 'string', length: 100)]
    public $name;

    /**
     * Category Id of asset.
     * 
     */
    #[DataType(type: 'int')]
    public $categoryId;

    /**
     * Type of asset.
     * 
     */
    #[DataType(type: 'int')]
    public $typeId;

    /**
     * Code for the asset.
     * 
     */
    #[DataType(type: 'string', length: 20)]
    public $code;

}
