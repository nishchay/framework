<?php

namespace Nishchay\Route\Annotation;

use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Utility\StringUtility;

/**
 * Routing annotation on controller.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Routing extends BaseAnnotationDefinition
{

    /**
     * Prefix parameter value.
     * 
     * @var string 
     */
    private $prefix;

    /**
     * Case for the route prefix.
     * 
     * @var string
     */
    private $case;

    /**
     *
     * @var string
     */
    private $pattern = false;

    /**
     * 
     * @param   string  $class
     * @param   array   $parameter
     */
    function __construct($class, $parameter)
    {
        parent::__construct($class, null);
        $priority = ['case', 'name'];
        $this->setter(ArrayUtility::customeKeySort($parameter, $priority), 'parameter');
    }

    /**
     * Returns prefix.
     * 
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Sets prefix.
     * 
     * @param string $prefix
     */
    protected function setPrefix($prefix)
    {
        $callback = ['lower' => 'strtolower', 'upper' => 'strtoupper', 'camel' => 'lcfirst'];
        if ($prefix === 'this.base') {
            $prefix = StringUtility::getExplodeLast('\\', $this->class);
        } else if (strpos($prefix, 'this.after') === 0) {
            $prefix = $this->replaceAfterName($prefix);
            if (array_key_exists($this->case, $callback)) {
                $prefix = implode('/', array_map($callback[$this->case], explode('\\', $prefix)));
            }
            $this->prefix = trim(str_replace('\\', '/', $prefix), '/');
            return;
        }
        $this->prefix = (array_key_exists($this->case, $callback) ?
                call_user_func($callback[$this->case], $prefix) :
                $prefix);
    }

    /**
     * Replaces whatever after this.after: with nothing and returns.
     * 
     * @param string $name
     * @return string
     */
    private function replaceAfterName($name)
    {
        return preg_replace('#^' . preg_quote(substr($name, strlen('this.after:'))) . '(.*)#', '$1', $this->class);
    }

    /**
     * Returns case.
     * 
     * @return string
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Sets case.
     * 
     * @param string $case
     * @return $this
     */
    protected function setCase($case)
    {
        $this->case = $case;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * 
     * @param string $pattern
     * @return $this
     */
    protected function setPattern(string $pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

}
