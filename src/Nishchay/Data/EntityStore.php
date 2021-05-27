<?php

namespace Nishchay\Data;

use Nishchay;
use Exception;
use AnnotationParser;
use ReflectionClass;

/**
 * Description of Entity Store.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2016, Nishchay Source
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class EntityStore
{

    /**
     * Collections of entities.
     * 
     * @var array 
     */
    private static $collection = [];

    /**
     * 
     * @param type $class
     * @return EntityClass
     * @throws Exception
     */
    protected function entity($class)
    {

        # Entities are process only once during each request
        # When it's usage is made at any point. Next request fetches entity
        # from persisted entities.
        if (array_key_exists($class, self::$collection)) {
            return self::$collection[$class];
        }

        if (Nishchay::getEntityCollection()->isExist($class) === FALSE) {
            throw new Exception('Class [' . $class . '] is not registered entity.', null, null, 911083);
        }

        self::$collection[$class] = $entity = new EntityClass($class, $this->getAnnotations($class));
        $entity->resolveDependency();
        return $entity;
    }

    /**
     * 
     * @param type $class
     * @return type
     */
    private function getAnnotations($class)
    {
        $reflection = new ReflectionClass($class);
        return AnnotationParser::getAnnotations($reflection->getDocComment());
    }

}
