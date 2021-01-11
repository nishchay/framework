<?php

namespace Nishchay\Route\Pattern;

use Nishchay\Processor\AbstractCollection;
use Nishchay\Route\Pattern\Action;
use Nishchay\Route\Pattern\ActionMethod;
use Nishchay\Route\Pattern\ActionMethodParameter;
use Nishchay\Route\Pattern\AbstractPattern;
use Nishchay\Route\Pattern\Crud;

/**
 * Route pattern collection.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.1
 * @author      Bhavik Patel
 */
class Collection extends AbstractCollection
{

    /**
     * List of route patterns.
     * 
     * @var array
     */
    private $collection = [
        'action' => Action::class,
        'actionmethod' => ActionMethod::class,
        'actionmethodparameter' => ActionMethodParameter::class,
        'crud' => Crud::class
    ];

    /**
     * Returns route pattern.
     * 
     * @param string $name
     * @return AbstractPattern
     */
    public function get(string $name)
    {
        $name = strtolower($name);
        if (array_key_exists($name, $this->collection) !== false) {
            $pattern = $this->collection[$name];

            return $pattern instanceof AbstractPattern ? $pattern : ($this->collection[$name] = new $pattern);
        }
        
        return $this->collection[$name] = new CustomPattern($name);
    }

    /**
     * Returns count of pattern.
     * 
     * @return int
     */
    public function count(): int
    {
        return $this->count($this->collection);
    }

}
