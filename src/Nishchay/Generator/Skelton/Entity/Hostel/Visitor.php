<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

/**
 * Hostel Visitor entity class.
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
class Visitor
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $visitorId;

    /**
     * Guest for whom visitor visited.
     * 
     * @DataType(type=int)
     */
    public $guestId;

    /**
     * Name of visitor.
     * 
     * @DataType(type=string, length=50)
     */
    public $name;

    /**
     * Reason for visit.
     * 
     * @DataType(type=string)
     */
    public $reason;

    /**
     * When visitor visited.
     * 
     * @DataType(type=datetime)
     */
    public $timeIn;

    /**
     * When visitor left.
     * 
     * @DataType(type=datetime)
     */
    public $timeOut;

}
