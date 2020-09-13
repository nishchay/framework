<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

/**
 * Hostel Student entity class.
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
class Guest
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $guestId;

    /**
     * First name of guest.
     * 
     * @DataType(type=string, length=100,encrypt=true)
     */
    public $firstName;

    /**
     * Last name of guest.
     * 
     * @DataType(type=string, length=50,encrypt=true)
     */
    public $lastName;

    /**
     * Contact number.
     * 
     * @DataType(type=int,encrypt=true)
     */
    public $contactNumber;

    /**
     * Birth date.
     * 
     * @DataType(type=date,encrypt=true)
     */
    public $birthdate;

    /**
     * Gender.
     * 
     * @DataType(type=string,value=[male,female],encrypt=true)
     */
    public $gender;

}
