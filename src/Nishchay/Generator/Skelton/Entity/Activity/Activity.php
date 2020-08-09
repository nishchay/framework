<?php

namespace Nishchay\Generator\Skelton\Entity\Activity;

/**
 * User activity entity class.
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
class Activity
{

    /**
     * @Identity
     * @DataType(type=int,readonly=true)
     */
    public $userActivityId;

    /**
     *
     * @DataType(type=int) 
     */
    public $userId;

    /**
     *
     * @DataType(type=string) 
     */
    public $name;
    
    /**
     *
     * @DataType(type=string)
     */
    public $description;

    /**
     *
     * @DataType(type=string) 
     */
    public $type;

    /**
     *
     * @DataType(type=datetime) 
     */
    public $createdAt;

}
