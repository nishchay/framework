<?php

namespace Nishchay\Generator\Skelton\Entity\Activity;

/**
 * Affected Entity entity class.
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
class AffectedEntity
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $affectedEntityId;

    /**
     *
     * @DataType(type=int)
     */
    public $activityId;

    /**
     *
     * @DataType(type=string)
     */
    public $tableName;

}
