<?php

namespace Nishchay\Generator\Skelton\Entity;

/**
 * Permission entity class.
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
class Permission
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $permissionId;

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

}
