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
     * @DataType(type=string,length=50,required=true) 
     */
    public $firstName;

    /**
     *
     * @DataType(type=string,length=50,required=true) 
     */
    public $lastName;

    /**
     *
     * @DataType(type=string,length=10,required=true) 
     */
    public $gender;
    
    /**
     *
     * @DataType(type=string,length=100,required=true)
     */
    public $email;
    
    /**
     *
     * @DataType(type=int)
     */
    public $isActive;

    /**
     *
     * @DataType(type=date) 
     */
    public $birthDate;

}
