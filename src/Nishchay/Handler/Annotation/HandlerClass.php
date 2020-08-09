<?php

namespace Nishchay\Handler\Annotation;

use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Handler\Annotation\Handler;

/**
 * Description of HandlerClass
 *
 * @author Bhavik Patel
 */
class HandlerClass extends BaseAnnotationDefinition
{

    /**
     *
     * @var \Nishchay\Handler\Annotation\Handler 
     */
    private $handler;

    public function __construct($class, $annotations)
    {
        parent::__construct($class, null);
        $this->setter($annotations);
    }

    /**
     * 
     * @return \Nishchay\Handler\Annotation\Handler 
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * 
     * @param type $handler
     */
    public function setHandler($handler)
    {
        $this->handler = new Handler($this->class, $handler);
    }

}
