<?php

namespace Nishchay\Validation\Rules;

use DateTime;

/**
 * Date validation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DateRule extends AbstractRule
{

    /**
     * Error messages.
     * 
     * @var array
     */
    protected static $messeges = [
        'format' => '{field} should be in format {0}.',
        'before' => '{field} should be before {0}.',
        'after' => '{field} should be after {0}.',
        'range' => '{field} should be between {0} and {1}.'
    ];

    /**
     * Date rule name.
     */
    const NAME = 'date';

    /**
     * Date format rule name.
     */
    const FORMAT_NAME = 'date:format';

    /**
     * Returns true if $value is as per $format.
     * It returns false if date is invalid.
     * 
     * @param string $value
     * @param string $format
     * @return boolean
     */
    public function format($value, $format): bool
    {
        $date = DateTime::createFromFormat($format, $value);
        return $date !== false && array_sum($date->getLastErrors()) === 0 && $date->format($format) === $value;
    }

    /**
     * Returns difference between date1 and date2
     * 
     * @param string|DateTime $date1
     * @param string|DateTime $date2
     * @param string|boolean $format
     * @return boolean
     */
    private function getDiff($date1, $date2, $format)
    {
        $date1 = is_string($date1) ?
                DateTime::createFromFormat($format, $date1) : $date1;
        $date2 = is_string($date2) ?
                DateTime::createFromFormat($format, $date2) : $date2;
        return $date1->diff($date2);
    }

    /**
     * Returns TRUE $date1 is before $date2.
     * 
     * @param string|DateTime $date1
     * @param string|DateTime $date2
     * @param string|boolean $format
     * @return boolean
     */
    public function before($date1, $date2, $format): bool
    {
        $diff = $this->getDiff($date1, $date2, $format);
        return $diff !== false && $diff->invert === 0;
    }

    /**
     * Returns TRUE if $date1 is after $date2.
     * 
     * @param string|DateTime $date1
     * @param string|DateTime $date2
     * @param string|boolean $format
     * @return boolean
     */
    public function after($date1, $date2, $format): bool
    {
        $diff = $this->getDiff($date1, $date2, $format);
        return $diff !== false && ($diff->days > 0 && $diff->invert === 1);
    }

    /**
     * Returns true if $match is in between $start and $end.
     * 
     * @param string|DateTime $match
     * @param string|DateTime $start
     * @param string|DateTime $end
     * @param string|boolean $format
     * @return boolean
     */
    public function range($match, $start, $end, $format): bool
    {
        if (is_string($format)) {
            $match = DateTime::createFromFormat($format, $match);
            $start = DateTime::createFromFormat($format, $start);
            $end = DateTime::createFromFormat($format, $end);
        }

        return $match >= $start && $match <= $end;
    }

    /**
     * Returns name of validation type.
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

}
