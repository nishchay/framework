<?php

namespace Nishchay\Generator\Skelton\Entity;

/**
 * Location entity class.
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
class Location
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $locationId;

    /**
     * Name of this address.
     * 
     * @DataType(type=string, length=100)
     */
    public $name;

    /**
     * Full address.
     * 
     * @DataType(type=string)
     */
    public $address;

    /**
     * Latitude of this address.
     * 
     * @DataType(type=float)
     */
    public $latitude;

    /**
     * Longitude of this address.
     * 
     * @DataType(type=float)
     */
    public $longitude;

}
