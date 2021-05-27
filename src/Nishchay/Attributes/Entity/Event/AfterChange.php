<?php

namespace Nishchay\Attributes\Entity\Event;

use Nishchay\Attributes\AttributeTrait;
use Nishchay\Exception\InvalidAttributeException;

/**
 * Description of AfterChange
 *
 * @author bhavik
 */
#[\Attribute]
class AfterChange
{

    use AttributeTrait {
        verify as parentVerify;
    }

    const NAME = 'afterChange';
    const SUPPORTED = ['insert', 'update', 'remove'];

    /**
     * 
     * @param array $for
     * @param string|array $callback
     * @param int $priority
     */
    public function __construct(private array $for = self::SUPPORTED,
            private string|array $callback = [], private int $priority = 0)
    {
        ;
    }

    public function verify()
    {
        $this->parentVerify();

        $this->refactorCallback()
                ->verifyForParameter();
    }

    /**
     * Refactors callback parameter
     * 
     */
    private function refactorCallback()
    {
        if (empty($this->callback)) {
            return $this;
        }

        # Ignoring callback parameter while annotation defined on method. This
        # method will be considered as callback.
        if ($this->method !== null) {
            $this->callback = $this->method;
        }

        if (is_array($this->callback)) {
            if (count($this->callback) !== 2) {
                throw new InvalidAttributeException('Invalid callback parameter'
                                . ' of [AfterChange] attribute.', $this->class,
                                $this->method);
            }
        } else {
            # Now we will prepend class name if only method has been set as 
            # callback. In this case, we consider class on which annotation
            # defined.
            if (strpos($this->callback, '::') === false) {
                $this->callback = "{$this->class}::{$this->callback}";
            }

            $this->callback = explode('::', $this->callback);
        }

        if (method_exists(...$this->callback) === false) {
            throw new InvalidAttributeException('Callback for [AfterChange]'
                            . ' event does not exists.', $this->class,
                            $this->method);
        }

        return $this;
    }

    /**
     * 
     * @throws InvalidAnnotationParameterException
     */
    private function verifyForParameter()
    {
        foreach ($this->for as $type) {
            if (!in_array($type, self::SUPPORTED)) {
                throw new InvalidAnnotationParameterException('Invalid'
                                . ' modification type [' . $type . '] for'
                                . ' [AfterChange] event.', $this->class,
                                $this->method, 911029);
            }
        }
        return $this;
    }

    /**
     * Returns callback class.
     * 
     * @return string
     */
    public function getCallbackClass()
    {
        return $this->callback[0];
    }

    /**
     * Returns callback method.
     * 
     * @return string
     */
    public function getCallbackMethod()
    {
        return $this->callback[1];
    }

    /**
     * Returns TRUE if this trigger is for insert.
     * 
     * @return boolean
     */
    public function isForInsert()
    {
        return in_array('insert', $this->for);
    }

    /**
     * Returns TRUE if this trigger is for update.
     * 
     * @return boolean
     */
    public function isForUpdate()
    {
        return in_array('update', $this->for);
    }

    /**
     * Returns TRUE if this trigger is for remove.
     * 
     * @return boolean
     */
    public function isForRemove()
    {
        return in_array('remove', $this->for);
    }

}
