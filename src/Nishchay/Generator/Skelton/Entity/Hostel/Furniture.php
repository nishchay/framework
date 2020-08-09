<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

/**
 * Hostel Furniture entity class.
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
class Furniture
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $furnitureId;

    /**
     *
     * @DataType(type=int)
     */
    public $hostelId;

    /**
     *
     * @DataType(type=int)
     */
    public $buildingId;

    /**
     *
     * @DataType(type=int)
     */
    public $roomId;

    /**
     *
     * @DataType(type=string)
     */
    public $furnitureName;

    /**
     *
     * @DataType(type=int)
     */
    public $amount;

    /**
     *
     * @DataType(type=int)
     */
    public $isActive;

    /**
     *
     * @DataType(type=string)
     */
    public $inactiveReason;

}
