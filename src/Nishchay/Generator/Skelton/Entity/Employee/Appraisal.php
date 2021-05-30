<?php

namespace Nishchay\Generator\Skelton\Entity\Employee;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Employee APpraisal entity class.
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
class Appraisal
{

    /**
     * Appraisal id.
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $appraisalId;

    /**
     * Employee id.
     * 
     */
    #[DataType(type: 'int')]
    public $employeeId;

    /**
     * Salary of employee.
     * This should be current salary.
     * 
     */
    #[DataType(type: 'int')]
    public $salary;

    /**
     * Percent of increase in salary.
     * 
     */
    #[DataType(type: 'int')]
    public $percent;

    /**
     * Value of increase in salary.
     * 
     */
    #[DataType(type: 'int')]
    public $amount;

    /**
     * Salary after appraisal.
     * 
     */
    #[DataType(type: 'int')]
    public $newSalary;

    /**
     * Is appraisal accepted by employee.
     * 
     */
    #[DataType(type: 'boolean', default: false)]
    public $accepted;

    /**
     * Appraisal offered at.
     * 
     */
    #[DataType(type: 'date')]
    public $offeredAt;

    /**
     * Appraisal effective from.
     * 
     */
    #[DataType(type: 'date')]
    public $effectiveAt;

}
