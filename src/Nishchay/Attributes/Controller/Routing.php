<?php

namespace Nishchay\Attributes\Controller;

use Nishchay\Attributes\AttributeTrait;
use Attribute;

/**
 * Routing attribute class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Routing
{

    use AttributeTrait {
        verify as parentVerify;
    }

    const NAME = 'routing';

    public function __construct(private ?string $prefix = null,
            private ?string $case = null, private ?string $pattern = null)
    {
        
    }

    /**
     * Sets prefix.
     * 
     */
    public function verify()
    {
        $this->parentVerify();
        $callback = ['lower' => 'strtolower', 'upper' => 'strtoupper', 'camel' => 'lcfirst'];
        if ($this->prefix === 'this.base') {
            $this->prefix = StringUtility::getExplodeLast('\\', $this->class);
        } else if (strpos($this->prefix, 'this.after') === 0) {
            $this->prefix = $this->replaceAfterName($this->prefix);
            if (array_key_exists($this->case, $callback)) {
                $this->prefix = implode('/',
                        array_map($callback[$this->case],
                                explode('\\', $this->prefix)));
            }
            $this->prefix = trim(str_replace('\\', '/', $this->prefix), '/');
            return;
        }
        $this->prefix = (array_key_exists($this->case, $callback) ?
                call_user_func($callback[$this->case], $this->prefix) :
                $this->prefix);
    }

    /**
     * Replaces whatever after this.after: with nothing and returns.
     * 
     * @param string $name
     * @return string
     */
    private function replaceAfterName($name)
    {
        return preg_replace('#^' . preg_quote(substr($name,
                                strlen('this.after:'))) . '(.*)#', '$1',
                $this->class);
    }

}
