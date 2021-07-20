<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Hostel Room entity class.
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
class Room
{

    /**
     * Room id
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $roomId;

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
     * Room number.
     * 
     */
    #[DataType(type: 'string')]
    public $number;

    /**
     * Capacity of guest a room can accommodate.
     * 
     */
    #[DataType(type: 'int')]
    public $capcity;

    /**
     * Is room fully occupied.
     * 
     */
    #[DataType(type: 'boolean')]
    public $isOccupied;

    /**
     * Fees for this room.
     * 
     */
    #[DataType(type: 'int')]
    public $fees;

}
