<?php

namespace Nishchay\Data;

use Nishchay\Attributes\Entity\Property\DataType;

/**
 * Tracker trait for entity.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class TrackerTrait
{

    /**
     * Time when record was created.
     * 
     */
    #[DataType(type: 'datetime', default: 'now')]
    public $createdAt;

    /**
     * Who created record.
     * 
     */
    #[DataType(type: 'int')]
    public $createdBy;

    /**
     * Time when last record was updated.
     * 
     * @DataType(type=datetime)
     */
    #[DataType(type: 'datetime')]
    public $updatedAt;

    /**
     * Who last updated record.
     * 
     */
    #[DataType(type: 'int')]
    public $updatedBy;

    /**
     * Time when record was deleted.
     * 
     * @DataType(type=datetime)
     */
    #[DataType(type: 'datetime')]
    public $deletedAt;

    /**
     * Who deleted record.
     * 
     */
    #[DataType(type: 'int')]
    public $deletedBy;

    /**
     * Is record deleted.
     * 
     */
    #[DataType(type: 'boolean', default: false)]
    public $isDeleted;

}
