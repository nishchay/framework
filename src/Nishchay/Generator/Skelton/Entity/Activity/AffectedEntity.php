<?php

namespace Nishchay\Generator\Skelton\Entity\Activity;

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
 * @Entity(name='this.base')
 */
class AffectedEntity
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $affectedEntityId;

    /**
     * Should be activity id of Activity entity.
     * 
     * @DataType(type=int)
     */
    public $activityId;

    /**
     * Entity name whose data has been updated.
     * 
     * @DataType(type=string)
     */
    public $entityName;

}
