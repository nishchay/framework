<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

/**
 * Hostel Room entity class.
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
class Room
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $roomId;

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
    public $number;

    /**
     *
     * @DataType(type=int)
     */
    public $capcity;

    /**
     *
     * @DataType(type=int)
     */
    public $occupied;

    /**
     *
     * @DataType(type=int)
     */
    public $fees;

}
