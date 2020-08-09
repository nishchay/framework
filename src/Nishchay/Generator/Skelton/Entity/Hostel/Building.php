<?php

namespace Nishchay\Generator\Skelton\Entity\Hostel;

/**
 * Hostel Building entity class.
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
class Building
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $buildingId;

    /**
     *
     * @DataType(type=string, length=50)
     */
    public $name;

    /**
     *
     * @DataType(type=string)
     */
    public $description;

    /**
     *
     * @DataType(type=int, required=true)
     */
    public $fees;

    /**
     *
     * @DataType(type=string)
     */
    public $location;

}
