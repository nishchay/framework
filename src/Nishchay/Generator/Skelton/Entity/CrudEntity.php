<?php

namespace Nishchay\Generator\Skelton\Entity;

/**
 * {CrudControllerClassDescription}
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
 * @Entity(name='this.base')
 */
class CrudEntity
{

    /**
     *
     * @Identity
     * @DataType(type=int,readonly=true)
     */
    public $identityId;

    /**
     *
     * @DataType(type=datetime)
     */
    public $createdAt;

    /**
     *
     * @DataType(type=int)
     */
    public $createdBy;

    /**
     *
     * @DataType(type=datetime)
     */
    public $updatedAt;

    /**
     *
     * @DataType(type=int)
     */
    public $updatedBy;

    /**
     *
     * @DataType(type=datetime)
     */
    public $deletedAt;

    /**
     *
     * @DataType(type=int)
     */
    public $deletedBy;

    /**
     *
     * @DataType(type=boolean,default=false)
     */
    public $isDeleted;

}
