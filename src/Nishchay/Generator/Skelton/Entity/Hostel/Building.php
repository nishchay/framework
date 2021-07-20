<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Hostel Building entity class.
 *
 * #ANN_START
 * @license     http://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 * #ANN_END
 * {authorName}
 * {versionNumber}
 * 
 */
#[Entity(name: 'this.base')]
class Building
{

    /**
     * Building id.
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $buildingId;

    /**
     * Hostel to which this building belongs.
     * 
     */
    #[DataType(type: 'int')]
    public $hostelId;

    /**
     * Name of building.
     * 
     */
    #[DataType(type: 'string', length: 50)]
    public $name;

    /**
     * Description of this building.
     * 
     */
    #[DataType(type: 'string')]
    public $description;

    /**
     * Fees for this building.
     * Keep zero to apply default hostel as mentioned in Hostel entity.
     * 
     */
    #[DataType(type: 'int', required: true)]
    public $fees;

    /**
     * Location of building.
     * 
     */
    #[DataType(type: 'string')]
    public $location;

}
