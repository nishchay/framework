<?php

namespace Nishchay\Generator\Skelton\Entity\Activity;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * User activity entity class.
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
class Activity
{

    /**
     * User activity id.
     * 
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $userActivityId;

    /**
     * User id who did this activity.
     * 
     * @DataType(type=int) 
     */
    #[DataType(type: 'int')]
    public $userId;

    /**
     * Name of the activity.
     * 
     */
    #[DataType(type: 'string')]
    public $name;

    /**
     * Description
     */
    #[DataType(type: 'string')]
    public $description;

    /**
     * Description of the activity.
     * 
     */
    #[DataType(type: 'string')]
    public $type;

    /**
     * When was activity happened.
     *  
     */
    #[DataType(type: 'datetime')]
    public $createdAt;

}
