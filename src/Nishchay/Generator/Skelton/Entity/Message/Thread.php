<?php

namespace Nishchay\Generator\Skelton\Entity\Message;

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
     * Thread name.
     * 
     * @DataType(type=string, length=100)
     */
    public $threadName;
    
    /**
     * Thread created at.
     * 
     * @DataType(type=datetime)
     */
    public $createdAt;
    
    /**
     * Who created thread.
     * 
     * @DataType(type=int)
     */
    public $creatdBy;

}
