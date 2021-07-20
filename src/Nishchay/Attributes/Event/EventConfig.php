<?php

namespace Nishchay\Attributes\Event;

use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Attributes\AttributeTrait;
use Nishchay\Processor\Names;

/**
 * Description of Intended
 *
 * @author bhavik
 */
#[\Attribute]
class EventConfig
{

    use AttributeTrait {
        verify as parentVerify;
    }

    /**
     * Attribute name.
     * 
     */
    const NAME = 'eventConfig';

    /**
     * Constant for before event.
     * 
     */
    const BEFORE = 'before';

    /**
     * Constant for after event.
     */
    const AFTER = 'after';

    public function __construct(private string $type, private string $when,
            private ?string $name = null, private bool $once = false)
    {
        
    }

    /**
     * Verify attribute parameters.
     * 
     * @return $this
     */
    public function verify()
    {
        $this->parentVerify();

        return $this->verifyType()
                        ->verifyWhen();
    }

    /**
     * Verifies type parameter.
     * 
     * @return $this
     * @throws InvalidAttributeException
     */
    protected function verifyType()
    {
        $this->type = strtolower($this->type);

        if (!in_array($this->type,
                        [Names::TYPE_GLOBAL, Names::TYPE_CONTEXT, Names::TYPE_SCOPE])) {
            throw new InvalidAttributeException('Event type [' . $this->type . ']'
                            . ' not supported.', $this->class, $this->method,
                            916002);
        }

        # Name parameter is required for event other than global
        if (in_array($this->type, [Names::TYPE_SCOPE, Names::TYPE_CONTEXT]) && $this->name === null) {
            throw new InvalidAttributeException('Parameter [name] parameter is required'
                            . ' when event type is [' . $this->type . '].',
                            $this->class, $this->method, 916003);
        }

        return $this;
    }

    /**
     * Verifies when parameter.
     * 
     * @return $this
     * @throws InvalidAttributeException
     */
    public function verifyWhen()
    {

        if (!in_array($this->when, [static::AFTER, static::BEFORE])) {
            throw new InvalidAttributeException('Value of parameter [when] should be after/before.',
                            $this->class, $this->method, 916001);
        }

        return $this;
    }

}
