<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

/**
 * Hostel Student entity class.
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
class Guest
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $guestId;

    /**
     *
     * @DataType(type=string, length=50)
     */
    public $firstName;

    /**
     *
     * @DataType(type=string, length=50)
     */
    public $lastName;

    /**
     *
     * @DataType(type=int)
     */
    public $contactNumber;

    /**
     *
     * @DataType(type=date)
     */
    public $birthdate;

    /**
     *
     * @DataType(type=string, length=10)
     */
    public $gender;

}
