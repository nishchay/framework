<?php

namespace Nishchay\Prototype;

use Nishchay;
use Closure;
use Nishchay\Data\EntityQuery;
use Nishchay\Http\Response\Response;
use Nishchay\Data\Reflection\DataClass;
use Nishchay\Security\Encrypt\EncryptTrait;
use Nishchay\Processor\FetchSingletonTrait;
use Nishchay\Data\EntityManager;

/**
 * Abstract prototype class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
abstract class AbstractPrototype
{

    use EncryptTrait,
        FetchSingletonTrait;

    /**
     * Entity class for login
     * 
     * @var string
     */
    protected $entityClass;

    /**
     * Form class name.
     * 
     * @var string
     */
    private $form;

    /**
     *
     * @var array 
     */
    private $ignoredFields = [];

    /**
     *
     * @var type 
     */
    private $fieldCallback = [];

    /**
     * 
     * @param string $entity
     */
    public function __construct(string $entity)
    {
        $this->entityClass = $entity;
    }

    /**
     * Validates form.
     * 
     * @return array|null
     */
    protected function validateForm()
    {
        if (($form = $this->getForm()) !== null) {
            if ($form->validate() === false) {
                Response::setStatus(HTTP_STATUS_BAD_REQUEST);
                $fields = ['errors' => $form->getErrors()];
                if ($form->getCSRF() !== false) {
                    $fields['csrf'] = $form->getCSRF()->getValue();
                }
                return $fields;
            }

            return true;
        }

        return null;
    }

    /**
     * Sets form class name.
     * 
     * @param string $class
     * @return $this
     */
    public function setForm(string $class): self
    {
        $this->form = $class;
        return $this;
    }

    /**
     * Returns entity class name.
     * 
     * @return string
     */
    protected function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * Returns instance of EntityManager class on $entityClass.
     *  
     * @return EntityManager
     */
    protected function getEntity(): EntityManager
    {
        return $this->getInstance(EntityManager::class, [$this->entityClass]);
    }

    /**
     * Returns form instance of $form.
     * 
     * @return \Nishchay\Form\Form
     */
    public function getForm()
    {
        if ($this->form === null) {
            return null;
        }
        return $this->getInstance($this->form);
    }

    /**
     * Returns instance of data class for entity.
     * 
     * @return DataClass
     */
    protected function getDataClass(): DataClass
    {
        return $this->getInstance(DataClass::class, [$this->entityClass]);
    }

    /**
     * Prevent form field to be inserted/updated to database.
     * 
     * @param string|array $fields
     * @return $this
     */
    public function setIgnoreFileds($fields): self
    {
        if (is_string($fields)) {
            $fields = [$fields];
        }

        foreach ($fields as $name) {
            $this->ignoredFields[] = $name;
        }

        return $this;
    }

    /**
     * Returns TRUE if field is added to list of ignore fields which should not
     * inserted or updated to database.
     * 
     * @param string $name
     * @return bool
     */
    protected function isIgnoredField($name): bool
    {
        return in_array($name, $this->ignoredFields);
    }

    /**
     * Sets field callback.
     * 
     * @param string $name
     * @param Closure $closure
     * @return $this
     */
    public function setFieldCallback($name, Closure $closure)
    {
        $this->fieldCallback[$name] = $closure;
        return $this;
    }

    /**
     * Returns field callback.
     * 
     * @param string $name
     * @return Closure
     */
    protected function getFieldCallback($name): ?Closure
    {
        return $this->fieldCallback[$name] ?? null;
    }

    /**
     * Formats value either by calling field callback or predefined format.
     * 
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    protected function formatvalue(string $name, $value)
    {
        if (($callback = $this->getFieldCallback($name)) !== null) {
            return call_user_func($callback, [$value]);
        }

        $types = Nishchay::getSetting('prototype.types');

        if (isset($types->{$name})) {
            switch ($types->{$name}) {
                case 'password':
                    return password_hash($value, PASSWORD_BCRYPT);
            }
        }

        return $value;
    }

    /**
     * Iterates over each form field and then it assigns value of form fields to
     * entity.
     */
    protected function prepareEntity()
    {
        foreach ($this->getForm()->getFormMethods() as $name) {
            $field = $this->getForm()->{$name}();

            $name = $field->getName();

            # Skipping inserting/updating to entity
            if ($this->isIgnoredField($name)) {
                continue;
            }

            $this->getEntity()->{$name} = $this->getEntity()
                    ->convert($name, $this->formatvalue($name, $field->getRequest()));
        }

        return $this;
    }

    /**
     * Saves entity. Returns value of identity if inserted otherwise returns
     * number of records updated.
     * 
     * @return int
     */
    protected function saveEntity()
    {
        return $this->getEntity()->save();
    }

    /**
     * Returns entity manager instance for the entity.
     * 
     * @return EntityQuery
     */
    abstract protected function getEntityQuery(): EntityQuery;
}
