<?php

namespace Nishchay\Generator\Skelton\Entity\User;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

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
 * 
 */
#[Entity(name: 'this.base')]
class User
{

    /**
     * User id
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $userId;

    /**
     * First name of user.
     * 
     */
    #[DataType(type: 'string', length: 50, required: true, encrypt: true)]
    public $firstName;

    /**
     * Last name of user.
     * 
     * @DataType(type=string,length=50,required=true,encrypt=true) 
     */
    #[DataType(type: 'string', length: 50, required: true, encrypt: true)]
    public $lastName;

    /**
     * Gender.
     * 
     */
    #[DataType(type: 'string', length: 50, values: ['male', 'female'],
                encrypt: true)]
    public $gender;

    /**
     * Email of user.
     * 
     */
    #[DataType(type: 'string', length: 100, required: true, encrypt: true)]
    public $email;

    /**
     * User password.
     * 
     */
    #[DataType(type: 'string', length: 200, required: true)]
    public $password;

    /**
     * Birth date.
     * 
     * @DataType(type=date,encrypt=true) 
     */
    #[DataType(type: 'date', encrypt: true)]
    public $birthDate;

    /**
     * Is user active.
     * 
     */
    #[DataType(type: 'boolean', default: true)]
    public $isActive;

    /**
     * Is user verified.
     * 
     */
    #[DataType(type: 'boolean', default: false)]
    public $isVerified;

    /**
     * Time when was user verified.
     * 
     */
    #[DataType(type: 'datetime')]
    public $verifiedAt;

}
