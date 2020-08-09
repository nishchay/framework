<?php

namespace Nishchay\Generator\Skelton\Entity\Asset;

/**
 * Asset Life cycle entity class.
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
class LifeCycle
{

    /**
     *
     * @Identity
     * @DataType(type=int, readonly=true)
     */
    private $lifeCycleId;

    /**
     *
     * @DataType(type=int, required=true) 
     */
    private $assetId;

    /**
     *
     * @DataType(type=boolean) 
     */
    private $isExpired;

    /**
     *
     * @DataType(type=datetime)
     */
    private $startAt;

    /**
     *
     * @DataType(type=datetime)
     */
    private $endAt;

    /**
     *
     * @DataType(type=float)
     */
    private $valueAfterCycle;

}
