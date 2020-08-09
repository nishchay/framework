<?php

namespace Nishchay\Processor\SetUp;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\AlreadyInstanciatedExecption;
use Nishchay\Handler\Detail;
use Nishchay\Processor\Facade;

/**
 * Sets connection for the Nishchay.
 * Checks compatability of the Nishchay,defines constants and register callbacks.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
final class Configure
{

    /**
     * Required minimum php version to run the Nishchay
     */
    const MINIMUM_VERSION = '7.2.0';

    /**
     * This class instance
     * 
     * @var object 
     */
    private static $instance;

    /**
     * 
     * @throws AlreadyInstanciatedExecption
     */
    public function __construct()
    {
        if (self::$instance !== null) {
            throw new AlreadyInstanciatedExecption('Application Configuration and setup preparation already been started.', null, null, 925003);
        }

        self::$instance = $this;
        $this->primarySetup();
    }

    /**
     * 
     */
    protected function registerCallbacks()
    {
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
    }

    /**
     * Checks for compatible version
     * Defines contants usable in most codes
     * 
     * @param type $directory
     */
    public function primarySetup()
    {
        if (version_compare(self::MINIMUM_VERSION, phpversion()) === 1) {
            throw new ApplicationException('Nishchay requires php version'
                    . ' greater than [' . self::MINIMUM_VERSION . '].', null, null, 925004);
        }

        # System File Path.
        define('SYS', dirname(__DIR__) . DS);

        # Configuration Path.
        define('CONFIG', SETTINGS . 'configuration' . DS);

        # Persistent folder path.
        define('PERSISTED', ROOT . 'persisted' . DS);

        $this->registerCallbacks();

        new Facade();
    }

    /**
     * Handler for error 
     * 
     * @param   string      $code
     * @param   string      $message
     * @param   string      $file
     * @param   int         $line
     * @return  boolean
     */
    public function errorHandler($code, $message, $file, $line, $trace, $type = null)
    {
        $types = [
            E_NOTICE => 'notice',
            E_WARNING => 'warning',
            E_USER_ERROR => 'error'
        ];
        $warning = [E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING,
            E_USER_DEPRECATED];
        $notice = [E_NOTICE, E_USER_NOTICE, E_DEPRECATED];
        if ($type === null) {
            if (isset($types[$code])) {
                $type = $types[$code];
            } else if (in_array($code, $warning)) {
                $type = 'warning';
            } else if (in_array($code, $notice)) {
                $type = 'notice';
            } else {
                $type = 'error';
            }
        }

        if (Nishchay::getSetting('logger.enable')) {
            $logLine = $type . ' ' . $message . ' on line ' . $line . ' in file ' . $file;
            Nishchay::getLogger()->error($logLine);
        }

        Nishchay::getExceptionHandler()
                ->handle(new Detail($code, $message, $file, $line, $type, $trace));
        return true;
    }

    /**
     * Handler for exception thrown
     * 
     * @param type $e
     */
    public function exceptionHandler($e)
    {
        $trace = $e->getTrace();

        if (!isset($trace['file']) || isset($e->custom)) {
            $file = $e->getFile();
            $line = $e->getLine();
        } else {
            $file = $trace['file'];
            $line = $trace['line'];
        }
        $this->errorHandler($e->getCode(), $e->getMessage(), $file, $line, $trace, get_class($e));
    }

}
