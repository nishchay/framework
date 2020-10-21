<?php

namespace Nishchay\Prototype;

use Nishchay\Prototype\AbstractPrototype;
use Nishchay\Data\EntityQuery;

/**
 * Crud prototype class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
class Crud extends AbstractPrototype
{

    /**
     * Returns instance EntityQuery.
     * 
     * @return EntityQuery
     */
    protected function getEntityQuery(): EntityQuery
    {
        return $this->getInstance(EntityQuery::class, [$this->entityClass]);
    }

    /**
     * Execute crud prototype.
     * 
     * @return array
     */
    public function execute()
    {
        $response = $this->validateForm();

        if (is_array($response)) {
            return $response;
        }
    }

}
