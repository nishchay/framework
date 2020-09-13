<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

/**
 * Hostel Visitor entity class.
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
class Visitor
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $visitorId;

    /**
     *
     * @DataType(type=int)
     */
    public $guestId;

    /**
     *
     * @DataType(type=string, length=50)
     */
    public $name;

    /**
     *
     * @DataType(type=string)
     */
    public $reason;

    /**
     *
     * @DataType(type=datetime)
     */
    public $timeIn;

    /**
     *
     * @DataType(type=datetime)
     */
    public $timeOut;

}
