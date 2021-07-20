<?php

namespace Nishchay\Generator\Skelton\Entity\Employee;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Employee salary entity class.
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
class Salary
{

    /**
     * Salary id
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $salaryId;

    /**
     * Employee id.
     * 
     */
    #[DataType(type: 'int')]
    public $employeeId;

    /**
     * Employee salary without earning and deduction.
     * 
     */
    #[DataType(type: 'int')]
    public $salary;

    /**
     * Earning for this salary.
     * 
     */
    #[DataType(type: 'int')]
    public $earning;

    /**
     * Deduction for this salary.
     * 
     */
    #[DataType(type: 'int')]
    public $deduction;

    /**
     * When was salary credited to employee.
     * 
     */
    #[DataType(type: 'date')]
    public $date;

    /**
     * Month of salary.
     * 
     */
    #[DataType(type: 'int')]
    public $month;

    /**
     * Year of salary.
     * 
     */
    #[DataType(type: 'int')]
    public $year;

    /**
     * Start date from when salary started calculation.
     * 
     */
    #[DataType(type: 'datetime')]
    public $startDate;

    /**
     * End date to which salary was calculated.
     * 
     */
    #[DataType(type: 'datetime')]
    public $endDate;

    /**
     * Total working days in start date and end date.
     * 
     */
    #[DataType(type: 'int')]
    public $workingDay;

    /**
     * Present days.
     * 
     */
    #[DataType(type: 'int')]
    public $prsentDay;

}
