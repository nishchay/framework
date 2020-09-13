<?php

namespace Nishchay\Generator\Skelton\Entity\User;

/**
 * User entity class.
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
class User
{

    /**
     * @Identity
     * @DataType(type=int,readonly=TRUE) 
     */
    public $id;

    /**
     * First name of user.
     * 
     * @DataType(type=string,length=50,required=true,encrypt=true) 
     */
    public $firstName;

    /**
     * Last name of user.
     * 
     * @DataType(type=string,length=50,required=true,encrypt=true) 
     */
    public $lastName;

    /**
     * Gender.
     * 
     * @DataType(type=string,length=10,value=[male,female],encrypt=true) 
     */
    public $gender;
    
    /**
     * Email of user.
     * 
     * @DataType(type=string,length=100,required=true,encrypt=true)
     */
    public $email;
    
    /**
     * User password.
     * 
     * @DataType(type=string,length=200,required=true)
     */
    public $password;

    /**
     * Birth date.
     * 
     * @DataType(type=date,encrypt=true) 
     */
    public $birthDate;
    
    /**
     * Is user active.
     * 
     * @DataType(type=boolean)
     */
    public $isActive;
    
    /**
     * Is user verified.
     * 
     * @DataType(type=boolean)
     */
    public $isVerified;

    /**
     * Time when was user verified.
     * 
     * @DataType(type=datetime)
     */
    public $verifiedAt;
}
