<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

/**
 * Hostel entity class.
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
class Hostel
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $hostelId;

    /**
     *
     * @DataType(type=string, length=100)
     */
    public $name;

    /**
     *
     * @DataType(type=int)
     */
    public $roomCount;

    /**
     *
     * @DataType(type=int)
     */
    public $guestCount;

    /**
     *
     * @DataType(type=string, length=100)
     */
    public $location;

    /**
     *
     * @DataType(type=int)
     */
    public $fees;

}
