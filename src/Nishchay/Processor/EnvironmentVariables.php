<?php

namespace Nishchay\Processor;

use Nishchay;

/**
 * Loads environment variable.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class EnvironmentVariables extends AbstractSingleton
{

    /**
     * Instance of this class.
     * 
     * @var self
     */
    protected static $instance;

    /**
     * Lists of environment from env file.
     * 
     * @var array
     */
    private $variables;

    /**
     * 
     * @return boolean
     */
    protected function onCreateInstance()
    {
        $this->init();
    }

    /**
     * Loads variable from file.
     * 
     * @return boolean
     */
    private function init()
    {
        $stage = Nishchay::getApplicationStage();

        # This happens when get method of this class called before application start.
        if ($stage === false) {
            return false;
        }

        $file = SETTINGS . 'env' . DS . $stage . '.ini';
        if (file_exists($file)) {
            $this->variables = parse_ini_file($file, false, INI_SCANNER_TYPED);
            return true;
        }
        return false;
    }

    /**
     * Returns variable from environment variable if it does not exists it
     * returns null.
     * 
     * @param string $name
     */
    public function get($name)
    {
        if ($this->variables === null) {
            if ($this->init() === false) {
                return false;
            }
        }
        if (array_key_exists($name, $this->variables)) {
            return $this->variables[$name];
        }

        return false;
    }

}
