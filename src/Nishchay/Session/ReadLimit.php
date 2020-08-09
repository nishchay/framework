<?php

namespace Nishchay\Session;

use Nishchay\Exception\ApplicationException;

/**
 * Session with lifetime of number of its read limit.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2016, Nishchay Source
 * @version     1.0
 * @author      Bhavik Patel
 */
class ReadLimit extends BaseSession
{

    /**
     * Read limit.
     * 
     * @var int 
     */
    private $accessLimit;

    /**
     * 
     * @param int $limit
     */
    public function __construct($limit)
    {
        parent::__construct('readLimit');
        $this->accessLimit = $limit;
    }

    /**
     * 
     * @param   string  $offset
     * @param   mixed   $value
     */
    public function offsetSet($offset, $value)
    {
        $session_value = [
            'limit' => $this->accessLimit,
            'read' => 0,
            'value' => $value
        ];

        parent::offsetSet($offset, $session_value);
    }

    /**
     * 
     * @param   string  $offset
     * @return  mixed
     */
    public function &offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            $session = $this->session[$offset];
            $session['read'] = $this->session[$offset]['read'] += 1;
            if ($session['read'] === $session['limit']) {
                $this->offsetUnset($offset);
                return $session['value'];
            }
            return $this->session[$offset]['value'];
        }

        throw new ApplicationException('Property [' . $offset . '] not found in [' .
                $this->sessionType . '] session.', null, null, 929009);
    }

    /**
     * 
     * @param   string  $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->session[$offset]);
    }

}
