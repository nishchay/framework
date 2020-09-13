<?php

namespace Nishchay\Generator\Skelton\Entity\User;

/**
 * User entity class.
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
class User
{

    /**
     * @Identity
     * @DataType(type=int,readonly=TRUE) 
     */
    public $id;

    /**
     *
     * @DataType(type=string,length=50,required=true,encrypt=true) 
     */
    public $firstName;

    /**
     *
     * @DataType(type=string,length=50,required=true,encrypt=true) 
     */
    public $lastName;

    /**
     *
     * @DataType(type=string,value=[male,female],required=true,encrypt=true) 
     */
    public $gender;
    
    /**
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
     *
     * @DataType(type=int)
     */
    public $isActive;

    /**
     *
     * @DataType(type=date,encrypt=true) 
     */
    public $birthDate;
    
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
