<?php

namespace Nishchay\Service;

use Nishchay\Exception\BadRequestException;
use Nishchay\Attributes\Controller\Method\Service;
use Nishchay\Http\Request\Request;

/**
 * Base service process class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class BaseServiceProcess
{

    /**
     * Fields demands.
     * 
     * @var bool|array 
     */
    protected $fields = false;

    /**
     *
     * @var Service
     */
    protected $service;

    /**
     * 
     * @return $this
     */
    protected function setFields()
    {
        $fields = Request::get('fields');
        if ($fields !== false) {
            if (mb_strlen($fields) === 0) {
                throw new BadRequestException('Empty field demand.', null, null, 928002);
            }
            $this->fields = explode(',', $fields);
        }
        return $this;
    }

    /**
     * Sets service class instance.
     * 
     * @return $this
     */
    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

}
