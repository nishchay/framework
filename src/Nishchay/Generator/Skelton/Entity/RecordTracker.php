<?php

namespace Nishchay\Generator\Skelton\Entity;

/**
 * Asset entity class.
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
class RecordTracker
{

    public $createdAt;
    public $createdBy;
    public $modifiedAt;
    public $modifiedBy;
    public $deletedAt;
    public $deletedBy;
    public $isDeleted;

}
