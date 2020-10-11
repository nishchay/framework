<?php

namespace Nishchay\Generator\Skelton\Entity\Activity;

/**
 * Affected property entity class.
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
class AffectedProperty
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $affectedPropertyId;

    /**
     *
     * @DataType(type=int)
     */
    public $affectedEntityId;

    /**
     * Property name which was changed.
     * 
     * @DataType(type=string, length=200)
     */
    public $propertyName;

    /**
     * Old value of this property.
     * 
     * @DataType(type=string)
     */
    public $oldValue;

    /**
     * New value of this property.
     * 
     * @DataType(type=string)
     */
    public $newValue;

}
