<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

/**
 * Hostel Fees entity class.
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
class Fees
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $feesId;

    /**
     *
     * @DataType(type=int, required=true)
     */
    public $hostelId;

    /**
     *
     * @DataType(type=int, required=true)
     */
    public $buildingId;

    /**
     *
     * @DataType(type=int, required=true)
     */
    public $amount;

    /**
     *
     * @DataType(type=string)
     */
    public $currency;

    /**
     *
     * @DataType(type=datetime)
     */
    public $paidAt;

    /**
     *
     * @DataType(type=int)
     */
    public $receiverId;

    /**
     *
     * @DataType(type=string, length=200)
     */
    public $receiverName;

}
