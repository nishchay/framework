<?php

namespace Nishchay\Generator\Skelton\Entity\Activity;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Affected Entity entity class.
 * User activity can alter many entity. Store list of entities affected in
 * this class and stored list of affected properties in AffectedProperty.
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
class AffectedEntity
{

    /**
     * Affected entity id.
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $affectedEntityId;

    /**
     * Should be activity id of Activity entity.
     * 
     */
    #[DataType(type: 'int')]
    public $activityId;

    /**
     * Entity name whose data has been updated.
     * 
     */
    #[DataType(type: 'string')]
    public $entityName;

}
