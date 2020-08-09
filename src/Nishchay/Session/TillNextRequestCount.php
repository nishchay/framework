<?php

namespace Nishchay\Session;

use Nishchay\Exception\ApplicationException;

/**
 * Session variable last long till given number of next request.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2016, Nishchay Source
 * @version     1.0
 * @author      Bhavik Patel
 */
class TillNextRequestCount extends BaseSession
{

    const NAME = 'nextRequest';

    /**
     *
     * @var int 
     */
    private $toNumber;

    /**
     * Initialization
     */
    public function __construct($number)
    {
        parent::__construct(self::NAME);
        $this->toNumber = $number;
    }

    /**
     * Sets session value.
     * 
     * @param   string  $offset
     * @param   mixed   $value
     */
    public function offsetSet($offset, $value)
    {
        $session_value = [
            'counter' => $this->toNumber,
            'value' => $value
        ];

        parent::offsetSet($offset, $session_value);
    }

    /**
     * Returns session value.
     * 
     * @param   string  $offset
     * @return  mixed
     */
    public function &offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            $session = $this->session[$offset];
            return $session['value'];
        }

        throw new ApplicationException('Property [' . $offset . '] not found'
                . ' in TillNextRequestCount session.', null, null, 929012);
    }

}
