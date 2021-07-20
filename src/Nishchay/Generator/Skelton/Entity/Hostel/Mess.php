<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Hostel Mess entity class.
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
class Mess
{

    /**
     * Mess id
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $messId;

    /**
     * Hostel id.
     * 
     */
    #[DataType(type: 'int')]
    public $hostelId;

    /**
     * Building id.
     * 
     */
    #[DataType(type: 'int')]
    public $buildingId;

    /**
     * Mess name.
     * 
     */
    #[DataType(type: 'string', length: 100)]
    public $messName;

}
