<?php

namespace Nishchay\Generator;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Generator\Skelton\Controller\EmptyController;
use Nishchay\Generator\Skelton\Controller\CrudController;
use Nishchay\Generator\Skelton\Controller\ProtoCrud;
use Nishchay\Generator\Skelton\Controller\TemplateMapper;
use Nishchay\Form\Form as NishchayForm;
use Nishchay\Generator\Entity as EntityGenerator;

/**
 * Controller generator class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Controller extends AbstractGenerator
{

    public function __construct($name)
    {
        parent::__construct($name, 'controller');
    }

    /**
     * Creates empty controller.
     */
    public function createEmpty()
    {
        return $this->createClass(EmptyController::class);
    }

    /**
     * Creates CRUD controller.
     * 
     * @return string
     */
    public function createCrud()
    {
        return $this->createClass(CrudController::class, [$this, 'writeRouteName']);
    }

    /**
     * Creates controller for the crud prototype.
     * 
     * @param type $entityClass
     * @param type $formClass
     * @param type $classBaseName
     * @return type
     */
    public function createCrudPrototype($entityClass, $formClass, $classBaseName)
    {
        return $this->createClass(ProtoCrud::class, function($content) use($entityClass, $formClass, $classBaseName) {

                    $formClassName = $classBaseName . 'Form';
                    $entityClassName = $classBaseName . 'Entity';
                    $search = [
                        NishchayForm::class,
                        EntityGenerator::class,
                        'Form::class',
                        'Entity::class'
                    ];
                    $replace = [
                        $formClass . ' as ' . $formClassName,
                        $entityClass . ' as ' . $entityClassName,
                        $formClassName . '::class',
                        $entityClassName . '::class'
                    ];
                    return str_replace($search, $replace, $this->writeRouteName($content));
                });
    }

    /**
     * 
     * @param type $content
     * @return type
     */
    protected function writeRouteName($content)
    {
        do {
            $routeName = $this->getInput('Enter route name: ', null);
        } while (strlen($routeName) === 0);
        return str_replace('#routeName#', $routeName, $content);
    }

    /**
     * Checks if class already been existed with same name.
     * 
     * @throws ApplicationException
     */
    protected function isClassExists()
    {
        $found = Nishchay::getControllerCollection()
                ->getClass($this->name);

        # Controller does not exist.
        if ($found !== false) {
            throw new ApplicationException('Controller already exist with name [' .
                    $this->name . '].', null, null, 933009);
        }
    }

    /**
     * Returns mapper for controller.
     * 
     * @return \Nishchay\Generator\Skelton\Controller\TemplateMapper
     */
    public function getMapper()
    {
        if ($this->templateMapper !== null) {
            return $this->templateMapper;
        }

        return $this->templateMapper = new TemplateMapper();
    }

}
