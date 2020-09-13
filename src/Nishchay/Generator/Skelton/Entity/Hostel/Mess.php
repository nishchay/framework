<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

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
 * @Entity(name='this.base')
 */
class Mess
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $messId;

    /**
     * Hostel id.
     * 
     * @DataType(type=int)
     */
    public $hostelId;

    /**
     * Building id.
     * 
     * @DataType(type=int)
     */
    public $buildingId;

    /**
     * Mess name.
     * 
     * @DataType(type=string, length=100)
     */
    public $messName;

}
