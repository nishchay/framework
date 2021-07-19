<?php

namespace Nishchay\Attributes\Handler;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Attributes\AttributeTrait;
use Nishchay\Processor\Names;

/**
 * Description of Handler
 *
 * @author bhavik
 */
#[\Attribute]
class Handler
{

    use AttributeTrait {
        verify as parentVerify;
    }

    const NAME = 'handler';

    public function __construct(private string $type,
            private ?string $name = null)
    {
        ;
    }

    public function verify()
    {
        $this->parentVerify();

        $this->verifyType();
    }

    /**
     * Sets type parameter value.
     * 
     */
    public function verifyType()
    {
        if (!in_array(strtolower($this->type),
                        [
                            Names::TYPE_CONTEXT,
                            Names::TYPE_GLOBAL,
                            Names::TYPE_SCOPE
                ])) {
            throw new NotSupportedException('Handler type [' . $this->type .
                            '] not supported.', $this->class, 919005);
        }

        $this->type = strtolower($this->type);

        if (in_array($this->type, [Names::TYPE_SCOPE, Names::TYPE_CONTEXT]) && $this->name === null) {
            throw new InvalidAttributeException('Parameter [name] required when'
                            . ' handler type is [' . $this->type . '].',
                            $this->class, null, 919001);
        }

        return $this;
    }

}
