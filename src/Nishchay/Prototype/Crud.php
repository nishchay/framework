<?php

namespace Nishchay\Prototype;

use Nishchay\Exception\{
    ApplicationException,
    BadRequestException
};
use Nishchay\Prototype\AbstractPrototype;
use Nishchay\Data\{
    EntityQuery,
    EntityManager
};
use Nishchay\Http\Request\Request;

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
     * Operation terminated as cause of before save callback returned failure.
     * 
     */
    const TERMINATED_BEFORE_SAVE = 'TERMINATED_BEFORE_SAVE';

    /**
     * Failed to insert record.
     * 
     */
    const FAILED_TO_INSERT = 'FAILED_TO_INSERT';

    /**
     * No change has been made to record.
     */
    const RECORD_REMAINED_SAME = 'RECORD_REMAINED_SAME';

    /**
     * Record not found.
     */
    const RECORD_NOT_FOUND = 'RECORD_NOT_FOUND';

    /**
     * Record could not be removed.
     */
    const RECORD_NOT_REMOVED = 'RECORD_NOT_REMOVED';

    /**
     * Value of identity id.
     * 
     * @var int
     */
    private $id;

    /**
     *
     * @var type 
     */
    private $entity;

    /**
     * Callback to be called before saving.
     * 
     * @var \Closure
     */
    private $before;

    /**
     * Callback to be called on success.
     * 
     * @var \Closure
     */
    private $onSuccess;

    /**
     * Callback to be called on failure.
     * 
     * @var \Closure
     */
    private $onFailure;

    /**
     * Flag for pagination is enabled or disabled.
     * 
     * @var bool
     */
    private $pagination = true;

    /**
     * Min limit for the pagination of records.
     * 
     * @var int
     */
    private $minLimit = 10;

    /**
     * Max limit for the pagination of records.
     * 
     * @var int 
     */
    private $maxLimit = 50;

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
     * Returns instance of EntityManager class on $entityClass.
     *  
     * @return EntityManager
     */
    protected function getEntity()
    {
        if ($this->entity !== null) {
            return $this->entity;
        }

        if ($this->id === null) {
            return parent::getEntity();
        }

        $entity = new EntityManager($this->entityClass);

        return $this->entity = $entity->get($this->id);
    }

    /**
     * 
     * @param \Closure $before
     * @return $this
     */
    public function setBefore(\Closure $before)
    {
        $this->before = $before;
        return $this;
    }

    /**
     * 
     * @param \Closure $onSuccess
     * @return $this
     */
    public function setOnSuccess(\Closure $onSuccess)
    {
        $this->onSuccess = $onSuccess;
        return $this;
    }

    /**
     * 
     * @param \Closure $onFailure
     * @return $this
     */
    public function setOnFailure(\Closure $onFailure)
    {
        $this->onFailure = $onFailure;
        return $this;
    }

    /**
     * Executes callback to be called before saving.
     * 
     * @return bool
     */
    public function executeBefore(): bool
    {
        if ($this->before !== null) {
            return call_user_func($this->before, [$this->getEntity()]);
        }

        return true;
    }

    /**
     * Executes callback to be called on success.
     * 
     * @return bool
     */
    public function executeOnSuccess(array $parametes)
    {
        if ($this->onSuccess !== null) {
            return call_user_func($this->onSuccess, $parametes);
        }

        return false;
    }

    /**
     * Executes callback to be called on failure.
     * 
     * @return bool
     */
    public function executeOnFailure(array $parametes)
    {
        if ($this->onFailure !== null) {
            return call_user_func($this->onFailure, $parametes);
        }

        return false;
    }

    /**
     * Inserts record.
     * 
     * @return array
     */
    public function insert()
    {

        if ($this->getForm() === null) {
            throw new ApplicationException('Form is required.', null, null);
        }

        $response = $this->validateForm();

        if (is_array($response)) {
            return $response;
        }

        $this->prepareEntity();

        if ($this->executeBefore() === false) {
            if (($response = $this->executeOnFailure(['code' => self::TERMINATED_BEFORE_SAVE])) === false) {
                throw new ApplicationException(message: 'Before save returned failure.',
                                code: 935009);
            }

            return $response;
        }

        $id = $this->saveEntity();

        if ($id) {
            if (($response = $this->executeOnSuccess([$id])) === false) {
                $response = [
                    $this->getDataClass()->getIdentity() => $id
                ];
            }
            return $response;
        }

        if (($response = $this->executeOnFailure(['code' => self::FAILED_TO_INSERT])) === false) {
            throw new ApplicationException(message: 'Failed to insert record.',
                            code: 935010);
        }

        return $response;
    }

    /**
     * Updates record.
     * 
     * @return array
     */
    public function update(int $id)
    {

        if ($this->getForm() === null) {
            throw new ApplicationException(message: 'Form is required.',
                            code: 935011);
        }

        $this->id = $id;

        if (($form = $this->getForm()) !== null) {
            $form->setMethod(Request::PUT);
        }

        $response = $this->validateForm();

        if (is_array($response)) {
            return $response;
        }

        $entity = $this->getEntity();

        if ($entity === false) {
            if (($response = $this->executeOnFailure(['code' => self::RECORD_NOT_FOUND])) === false) {
                throw new BadRequestException(message: 'Record does not exists.',
                                code: 935012);
            }

            return $response;
        }

        $this->prepareEntity();

        if ($this->executeBefore() === false) {
            if (($response = $this->executeOnFailure(['code' => self::TERMINATED_BEFORE_SAVE])) === false) {
                throw new ApplicationException(message: 'Before save returned failure.', code: 935013);
            }

            return $response;
        }

        if ($this->saveEntity()) {
            if (($response = $this->executeOnSuccess([$id])) === false) {
                $response = [
                    'message' => 'Record has been updated'
                ];
            }
            return $response;
        }

        if (($response = $this->executeOnFailure(['code' => self::RECORD_REMAINED_SAME])) === false) {
            $response = [
                'message' => 'Record detail remained same.'
            ];
        }

        return $response;
    }

    /**
     * Removes record.
     * 
     * @param int $id
     * @return string
     * @throws BadRequestException
     */
    public function remove(int $id)
    {
        $this->id = $id;

        $entity = $this->getEntity();

        if ($entity === false) {
            if (($response = $this->executeOnFailure(['code' => self::RECORD_NOT_FOUND])) === false) {
                throw new BadRequestException(message: 'Record does not exists.', code: 935014);
            }

            return $response;
        }

        if ($entity->remove()) {
            if (($response = $this->executeOnSuccess([$id])) === false) {
                $response = [
                    'message' => 'Record has been removed'
                ];
            }
            return $response;
        }

        if (($response = $this->executeOnFailure(['code' => self::RECORD_NOT_REMOVED])) === false) {
            $response = [
                'message' => 'Could not remove record'
            ];
        }
    }

    /**
     * Returns details of record of given id.
     * 
     * @param int $id
     * @return type
     * @throws BadRequestException
     */
    public function getOne(int $id)
    {
        $this->id = $id;

        $entity = $this->getEntity();

        if ($entity === false) {
            if (($response = $this->executeOnFailure(['code' => self::RECORD_NOT_FOUND])) === false) {
                throw new BadRequestException(message: 'Record does not exists.', code: 935015);
            }

            return $response;
        }

        return $entity->getData('array');
    }

    /**
     * Enable or disable pagination.
     * 
     * @param bool $flag
     */
    public function pagination(bool $flag)
    {
        $this->pagination = $flag;
    }

    /**
     * Returns offset from the request.
     * 
     * @return int
     */
    private function getOffset(): int
    {
        if (Request::get('offset')) {
            $offset = (int) Request::get('offset');

            return $offset < 0 ? 0 : $offset;
        }

        return 0;
    }

    /**
     * Returns limit for the pagination.
     * 
     * @return int
     */
    private function getLimit(): int
    {
        if (Request::get('limit')) {
            $limit = (int) Request::get('limit');

            return $limit < $this->minLimit ? $this->minLimit :
                    ($limit > $this->maxLimit ? $this->maxLimit : $limit);
        }

        return $this->minLimit;
    }

    /**
     * Set min limit for the pagination.
     * 
     * @param int $minLimit
     * @return $this
     */
    public function setMinLimit(int $minLimit)
    {
        $this->minLimit = $minLimit;
        return $this;
    }

    /**
     * Set max limit for the pagination.
     * 
     * @param int $maxLimit
     * @return $this
     */
    public function setMaxLimit(int $maxLimit)
    {
        $this->maxLimit = $maxLimit;
        return $this;
    }

    /**
     * Returns record.
     * 
     * @param type $only
     * @return type
     */
    public function get($only = [])
    {
        $entityQuery = $this->getEntity()
                ->getEntityQuery(empty($only) ? true : $only);

        if ($this->pagination) {
            $entityQuery->setLimit($this->getLimit(), $this->getOffset());
        }

        return $entityQuery->get()->getAsArray(true, $only);
    }

}
