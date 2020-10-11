<?php

namespace Nishchay\Generator\Skelton\Entity\Employee;

/**
 * Employee entity class.
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
class Employee
{

    /**
     * @Identity
     * @DataType(type=int,readonly=true) 
     */
    public $employeeId;

    /**
     * First name of employee.
     * 
     * @DataType(type=string,length=100,encrypt=true)
     */
    public $firstName;

    /**
     * Last name of employee.
     * 
     * @DataType(type=string,length=100,encrypt=true)
     */
    public $lastName;

    /**
     * Gender of employee.
     * 
     * @DataType(type=string,value=[male,female],encrypt=true)
     */
    public $gender;

    /**
     * Birth date
     * 
     * @DataType(type=date,encrypt=true)
     */
    public $birthDate;

    /**
     * Join date.
     * 
     * @DataType(type=datetime)
     */
    public $joinDate;

    /**
     * Department id.
     * 
     * @DataType(type=int)
     */
    public $departmentId;

    /**
     * Position id.
     * 
     * @DataType(type=int)
     */
    public $positionId;

    /**
     * Employee current salary.
     * 
     * 
     * @DataType(type=int)
     */
    public $salary;

    /**
     * Date when employee left organization.
     * 
     * @DataType(type=date,encrypt=true)
     */
    public $leaveDate;

    /**
     * Reason of leaving organization.
     * 
     * @DataType(type=string)
     */
    public $leaveReason;

}
