<?php

namespace Nishchay\Generator\Skelton\Entity\User;

/**
 * Session entity class.
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
class Session
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    public $sessionId;

    /**
     *
     * @DataType(type=string, length=200,required=true)
     */
    public $sessionKey;

    /**
     *
     * @DataType(type=int, required=true)
     */
    public $userId;

    /**
     *
     * @DataType(type=string, length=50)
     */
    public $ip;

    /**
     *
     * @DataType(type=string, length=200)
     */
    public $client;

    /**
     *
     * @DataType(type=datetime)
     */
    public $start;

    /**
     *
     * @DataType(type=datetime)
     */
    public $end;

    /**
     *
     * @DataType(type=int)
     */
    public $active;

}
