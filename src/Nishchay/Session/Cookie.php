<?php

namespace Nishchay\Session;

/**
 * Cookie class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Cookie extends BaseCookie
{

    /**
     * 
     */
    public function __construct()
    {
        parent::__construct('cookie');
    }


}
