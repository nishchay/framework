<?php

namespace Nishchay\Generator\Skelton\Entity\User;

/**
 * User permission entity class.
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
class UserPermission
{

    /**
     * @Identity
     * @DataType(type=int, readonly=true) 
     */
    public $userPermissionId;

    /**
     * Permission id.
     * 
     * @DataType(type=int)
     */
    public $permissionId;

    /**
     * User id.
     * 
     * @DataType(type=int)
     */
    public $userId;

}
