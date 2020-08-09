<?php

namespace Nishchay\Processor;

use Nishchay\Session\BaseSession;

/**
 * Description of Session
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class InternalSession extends BaseSession
{

    /**
     * Internal session name.
     * 
     */
    const NAME = 'Nishchay';

    public function __construct()
    {
        parent::__construct(self::NAME);
    }

}
