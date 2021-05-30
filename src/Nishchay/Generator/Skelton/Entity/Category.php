<?php

namespace Nishchay\Generator\Skelton\Entity;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Category entity class.
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
class Category
{

    /**
     * Category id
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $categoryId;

    /**
     * Name of category.
     * 
     */
    #[DataType(type: 'string', length: 100)]
    public $name;

    /**
     * Code of category.
     * 
     */
    #[DataType(type: 'string', length: 10)]
    public $code;

    /**
     * Description of category.
     * 
     */
    #[DataType(type: 'string')]
    public $description;

}
