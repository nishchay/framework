<?php

namespace Nishchay\Generator\Skelton\Entity;

/**
 * Location entity class.
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
class Location
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $locationId;

    /**
     *
     * @DataType(type=string, length=100)
     */
    public $name;

    /**
     *
     * @DataType(type=string)
     */
    public $address;

    /**
     *
     * @DataType(type=float)
     */
    public $latitude;

    /**
     *
     * @DataType(type=float)
     */
    public $longitude;

}
