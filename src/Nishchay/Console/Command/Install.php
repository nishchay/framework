<?php

namespace Nishchay\Console\Command;

use Nishchay\Console\AbstractCommand;
use Nishchay\Generator\Installer;

/**
 * Installer class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Install extends AbstractCommand
{

    /**
     * Initialization.
     * 
     * @param array $arguments
     */
    public function __construct($arguments = [])
    {
        parent::__construct($arguments);
    }

    /**
     * 
     */
    protected function printList()
    {
        (new Installer())->install();
    }

}
