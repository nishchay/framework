<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

/**
 * Hostel Furniture entity class.
 *
 * #ANN_START
 * @license     http://Nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 * #ANN_END
 * {authorName}
 * {versionNumber}
 * @Entity(name='this.base')
 */
class Furniture
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $furnitureId;

    /**
     * Hostel Id.
     * 
     * @DataType(type=int)
     */
    public $hostelId;

    /**
     * Building Id.
     * 
     * @DataType(type=int)
     */
    public $buildingId;

    /**
     * Room Id.
     * 
     * @DataType(type=int)
     */
    public $roomId;

    /**
     * Furniture name.
     * 
     * @DataType(type=string)
     */
    public $furnitureName;

    /**
     * Amount of this furniture.
     * 
     * @DataType(type=int)
     */
    public $amount;

    /**
     * Is furniture being used.
     * 
     * @DataType(type=int)
     */
    public $isActive;

    /**
     * Reason for furniture not being used.
     * 
     * @DataType(type=string)
     */
    public $inactiveReason;

}
