<?php

namespace Nishchay\Session\SaveHandler;

use SessionHandlerInterface;

/**
 * Abstract save handler class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractSaveHandler implements SessionHandlerInterface {

    /**
     * Closes session write.
     */
    public function __destruct() {
        @session_write_close();
    }

}
