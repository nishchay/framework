<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

/**
 * Hostel Fees entity class.
 *
 * #ANN_START
 * @license     http://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 * #ANN_END
 * {authorName}
 * {versionNumber}
 * @Entity(name='this.base')
 */
class Fees
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $feesId;

    /**
     * Hostel  Id.
     * 
     * @DataType(type=int, required=true)
     */
    public $hostelId;

    /**
     * Building Id.
     * 
     * @DataType(type=int, required=true)
     */
    public $buildingId;

    /** 
     * Guest Id.
     * 
     * @DataType(type=int, required=true)
     */
    public $guestId;

    /**
     * Amount paid.
     * 
     * @DataType(type=int, required=true)
     */
    public $amount;

    /**
     * Currency of  paid amount.
     * 
     * @DataType(type=string)
     */
    public $currency;

    /**
     * Paid at.
     * 
     * @DataType(type=datetime)
     */
    public $paidAt;

    /**
     * Who received fees.
     * 
     * @DataType(type=int)
     */
    public $receiverId;

    /**
     * Receiver name if any.
     * 
     * @DataType(type=string, length=200)
     */
    public $receiverName;

}
