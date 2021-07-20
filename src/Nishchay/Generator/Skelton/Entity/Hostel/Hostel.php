<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Hostel entity class.
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
class Hostel
{

    /**
     * Hostel id
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $hostelId;

    /**
     * Name of hostel.
     * 
     */
    #[DataType(type: 'string', length: 100)]
    public $name;

    /**
     * Number of rooms in hostel.
     * 
     */
    #[DataType(type: 'int')]
    public $roomCount;

    /**
     * Number of guests in hostel.
     * 
     */
    #[DataType(type: 'int')]
    public $guestCount;

    /**
     * Location of hostel. Should be address.
     * 
     */
    #[DataType(type: 'string')]
    public $location;

    /**
     * Fees for this hostel.
     * 
     */
    #[DataType(type: 'int')]
    public $fees;

}
