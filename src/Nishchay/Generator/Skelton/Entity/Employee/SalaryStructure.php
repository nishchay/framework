<?php

namespace Nishchay\Generator\Skelton\Entity\Employee;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Employee salary structure entity class.
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
class SalaryStructure
{

    /**
     * Salary structure id
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $salaryStructureId;

    /**
     * Employee id.
     * 
     */
    #[DataType(type: 'int')]
    public $employeeId;

    /**
     * Value of this structure.
     * 
     */
    #[DataType(type: 'int')]
    public $value;

    /**
     * Fixed or percent.
     * 
     */
    #[DataType(type: 'string', values: ['fixed', 'percent'])]
    public $type;

    /**
     * Percent value from salary or else.
     * In the case of fixed enter fixed amount.
     * 
     */
    #[DataType(type: 'string')]
    public $valueFrom;

    /**
     * TRUE for earning.
     * FALSE for deduction.
     * 
     */
    #[DataType(type: 'boolean')]
    public $earning;

    /**
     * Whether this is life time or for the specific period.
     * 
     */
    #[DataType(type: 'string', values: ['permanent', 'temporary'])]
    public $specific;

    /**
     * Start date from when this earning or deduction need to be considered.
     * 
     */
    #[DataType(type: 'date')]
    public $speicifcStart;

    /**
     * End date when this earning or deduction need to be end.
     * 
     */
    #[DataType(type: 'date')]
    public $specificEnd;

}
