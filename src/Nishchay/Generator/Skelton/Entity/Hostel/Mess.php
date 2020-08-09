<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

/**
 * Hostel Mess entity class.
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
class Mess
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $messId;

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
     * @DataType(type=string, length=100)
     */
    public $messName;

}
