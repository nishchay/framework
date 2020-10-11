<?php

namespace Nishchay\Generator\Skelton\Entity\Asset;

/**
 * Asset Life cycle entity class.
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
class LifeCycle
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    private $lifeCycleId;

    /**
     * Asset id.
     * 
     * @DataType(type=int, required=true) 
     */
    private $assetId;

    /**
     * Flag for if asset has been expired.
     * 
     * @DataType(type=boolean) 
     */
    private $isExpired;

    /**
     * When was asset purchased or created.
     * 
     * @DataType(type=datetime)
     */
    private $startAt;

    /**
     * Asset expiration time.
     * 
     * @DataType(type=datetime)
     */
    private $endAt;

    /**
     * Value after expiration time.
     * 
     * @DataType(type=float)
     */
    private $valueAfterCycle;

}
