<?php

namespace Nishchay\Attributes\Event;

use Nishchay\Attributes\AttributeTrait;
use Attribute;

/**
 * Before event attribute class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
#[Attribute(Attribute::TARGET_METHOD)]
class BeforeEvent
{

    use AttributeTrait;

    const NAME = 'beforeEvent';

    /**
     *
     * @var type 
     */
    private $fired = false;

    public function __construct(private ?string $callback = null,
            private bool $once = false, private array $order = [])
    {
        ;
    }

    /**
     * Returns callback to call if defined.
     * 
     * @return  array
     */
    public function getCallback()
    {
        if ($this->callback === null) {
            return false;
        }

        if (strpos($this->callback, '::') !== false) {
            $callback = explode('::', $this->callback);
        } else {
            $callback = [$this->class, $this->callback];
        }
        return $callback;
    }

    /**
     * Mark event has been fired.
     * 
     */
    public function markFired()
    {
        $this->fired = true;
    }

    /**
     * Returns true if event is fired.
     * 
     * @return boolean
     */
    public function isFired()
    {
        return $this->fired;
    }

}
