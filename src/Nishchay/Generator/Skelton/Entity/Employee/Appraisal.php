<?php

namespace Nishchay\Generator\Skelton\Entity\Employee;


/**
 * Employee APpraisal entity class.
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
class Appraisal
{

    /**
     * @Identity
     * @DataType(type=int)
     */
    public $appraisalId;

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
    public $percent;

    /**
     * @DataType(type=int)
     */
    public $amount;

    /**
     * @DataType(type=int)
     */
    public $newSalary;

    /**
     * @DataType(type=int)
     */
    public $accepted;

    /**
     * @DataType(type=date)
     */
    public $offeredAt;

    /**
     * @DataType(type=date)
     */
    public $effectiveAt;

}
