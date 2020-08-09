<?php

namespace Nishchay\Logger\SaveHandler;

use Nishchay\Exception\NotSupportedException;
use DateTime;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * Abstract save Handler for Logger.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractSaveHandler
{

    use MethodInvokerTrait;

    /**
     * Returns name as per given file name format.
     * 
     * @param string $fileFormat
     * @return string
     * @throws NotSupportedException
     */
    protected function getName(string $fileFormat)
    {
        $callback = [$this, 'get' . ucfirst(strtolower($fileFormat))];
        if ($this->isCallbackExist($callback)) {
            return $this->invokeMethod($callback, [$fileFormat]);
        }
        throw new NotSupportedException('File format for logger save handler [' .
                ucfirst(strtolower($fileFormat)) . '] not supported.', null, null, 922001);
    }

    /**
     * Returns date of today.
     * 
     * @return string
     */
    public function getDate(): string
    {
        return (new DateTime)->format('Y-m-d');
    }

    /**
     * Return start date of this week.
     * 
     * @return string
     */
    public function getWeek(): string
    {
        return (new DateTime)->modify('this week');
    }

    /**
     * Returns start date of current biweek.
     * 
     * @return string
     */
    public function getBiweek(): string
    {
        $date = new DateTime;
        return $date->format('Y-m') . ($date->format('d') <= 15 ? '01' : '15');
    }

    /**
     * Returns start date of current month.
     * 
     * @return string
     */
    public function getMonth(): string
    {
        return (new DateTime)->modify('first day of this month');
    }

    public abstract function open();

    public abstract function write(string $type, string $logLine);

    public abstract function close();
}
