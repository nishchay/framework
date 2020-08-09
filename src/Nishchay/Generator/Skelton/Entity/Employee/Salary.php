<?php

namespace Nishchay\Generator\Skelton\Entity\Employee;

/**
 * Employee salary entity class.
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
class Salary
{

    /**
     * @Identity
     * @DataType(type=int,readonly=TRUE)
     */
    public $salaryId;

    /**
     * @DataType(type=int)
     */
    public $employeeId;

    /**
     * @DataType(type=int)
     */
    public $salary;

    /**
     * @DataType(type=int)
     */
    public $earning;

    /**
     * @DataType(type=int)
     */
    public $deduction;

    /**
     * @DataType(type=date)
     */
    public $date;

    /**
     * @DataType(type=int)
     */
    public $month;

    /**
     * @DataType(type=int)
     */
    public $year;
    /**
     *
     * @DataType(type=datetime)
     */
    public $startDate;
    
    /**
     *
     * @DataType(type=datetime)
     */
    public $endDate;

    /**
     * @DataType(type=int)
     */
    public $workingDay;

    /**
     * @DataType(type=int)
     */
    public $prsentDay;

}
