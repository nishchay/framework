<?php

namespace Nishchay\Generator\Skelton\Entity;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Permission entity class.
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
class Permission
{

    /**
     * Permission id
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $permissionId;

    /**
     * Name of permission.
     * 
     */
    #[DataType(type: 'string', length: 50)]
    public $name;

    /**
     * Description of permission.
     * 
     * @DataType(type=string)
     */
    #[DataType(type: 'string')]
    public $description;

}
