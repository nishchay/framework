<?php

namespace Nishchay\Generator\Skelton\Entity\Message;

/**
 * Thread entity class.
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
class Thread
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $threadId;

    /**
     *
     * @DataType(type=string, length=100)
     */
    public $threadName;
    
    public $createdAt;
    public $creatdBy;

}
