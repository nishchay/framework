<?php

namespace Nishchay\Generator;

use Nishchay;
use Nishchay\Generator\Skelton\Entity\EmptyEntity;
use Nishchay\Generator\Skelton\Entity\CrudEntity;
use Nishchay\Utility\Coding;
use Nishchay\Generator\Skelton\Entity\TemplateMapper;

/**
 * Entity Generator class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Entity extends AbstractGenerator
{

    public function __construct($name)
    {
        parent::__construct($name, 'entity');
    }

    /**
     * Returns Template mapper for entity.
     * 
     * @return TemplateMapper
     */
    public function getMapper()
    {
        if ($this->templateMapper !== null) {
            return $this->templateMapper;
        }

        return $this->templateMapper = new TemplateMapper();
    }

    /**
     * Returns true if class with name already exists.
     * 
     * @return type
     */
    protected function isClassExists()
    {
        return Nishchay::getEntityCollection()->isExist($this->name);
    }

    /**
     * Creates empty entity class with only identity property in it.
     * 
     * @return type
     */
    public function createEmpty()
    {
        return $this->createClass(EmptyEntity::class, [$this, 'writeIdentityId']);
    }

    /**
     * Creates entity class with CRUD related properties.
     * 
     * @return string
     */
    public function createCrud()
    {
        return $this->createClass(CrudEntity::class, [$this, 'writeIdentityId']);
    }

    /**
     * Renames identityId property with property name as per new entity class name.
     * 
     * @param string $content
     * @return string
     */
    protected function writeIdentityId($content)
    {
        return str_replace('identityId', lcfirst(Coding::getClassBaseName($this->name)) . 'Id', $content);
    }

}
