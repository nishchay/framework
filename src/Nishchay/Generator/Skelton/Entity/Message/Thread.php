<?php

namespace Nishchay\Generator\Skelton\Entity\Message;

use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Property\{
    Identity,
    DataType
};

/**
 * Thread entity class.
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
class Thread
{

    /**
     * Thread id
     */
    #[Identity]
    #[DataType(type: 'int', readOnly: true)]
    public $threadId;

    /**
     * Thread name.
     * 
     */
    #[DataType(type: 'string', length: 100)]
    public $threadName;

    /**
     * Thread created at.
     * 
     */
    #[DataType(type: 'datetime')]
    public $createdAt;

    /**
     * Who created thread.
     * 
     */
    #[DataType(type: 'int')]
    public $creatdBy;

}
