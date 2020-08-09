<?php

namespace Nishchay\Generator\Skelton\Entity\Employee;

/**
 * Employee salary structure entity class.
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
class SalaryStructure
{

    public $salaryStructureId;
    public $employeeId;
    public $value;

    /**
     * Fixed or percent.
     * @var type 
     */
    public $type;

    /**
     * Percent value from salary or else.
     * @var type 
     */
    public $valueFrom;

    /**
     * TRUE for earning.
     * FALSE for deduction.
     * @var type 
     */
    public $earning;
    public $specific;
    public $speicifcStart;
    public $specificEnd;

}
