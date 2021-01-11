<?php


namespace Nishchay\Generator\Skelton\Entity;

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
 * @Entity(name='this.base')
 */
class Category
{
    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $categoryId;
    /**
     * Name of category.
     * 
     * @DataType(type=string, length=100)
     */
    public $name;
    
    /**
     * Code of category.
     * 
     * @DataType(type=string, length=10)
     */
    public $code;
    
    /**
     * Description of category.
     * 
     * @DataType(type=stringÏ)
     */
    public $description;
}
