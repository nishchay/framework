<?php

namespace Nishchay\Session;

/**
 * Description of Session
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Session extends BaseSession
{

    /**
     * 
     */
    public function __construct()
    {
        parent::__construct('normal');
    }

}
