<?php

namespace Nishchay\Handler\Annotation;

use Nishchay\Exception\ApplicationException;
use Nishchay\Attributes\Handler\Handler;
use Nishchay\Attributes\AttributeTrait;

/**
 * Description of HandlerClass
 *
 * @author Bhavik Patel
 */
class HandlerClass
{

    use AttributeTrait;

    /**
     *
     * @var Handler 
     */
    private $handler;

    /**
     * 
     * @param string $class
     * @param array $attributes
     * @throws ApplicationException
     */
    public function __construct(string $class, array $attributes)
    {
        $this->setClass($class);
        $this->processAttributes($attributes);

        if ($this->handler === null) {
            throw new ApplicationException('[' . $class . '] must be handler',
                            $class);
        }
    }

    /**
     * 
     * @param Handler $handler
     */
    public function setHandler(Handler $handler)
    {
        $this->handler = $handler;
    }

}
