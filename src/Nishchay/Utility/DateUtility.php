<?php

namespace Nishchay\Utility;

use DateTime;

/**
 * Coding utility class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DateUtility
{

    /**
     * MySQL Datetime format.
     */
    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * MySQL date formate
     */
    const MYSQL_DATE_FORMAT = 'Y-m-d';

    /**
     * Returns DateTime instance.
     * Returns FALSE if there was last error and $ignoreLastError = FALSE
     * 
     * @param string $format
     * @param string $time
     * @param boolean $ignoreLastError
     */
    public static function createFromFormat(string $format, $time, bool $ignoreLastError = false)
    {
        $date = DateTime::createFromFormat($format, $time);
        if ($date instanceof DateTime) {
            if (array_sum($date->getLastErrors()) > 0 && $ignoreLastError === false) {
                return false;
            }
            return $date;
        }

        return false;
    }

    /**
     * Formats given date to format.
     * 
     * @param DateTime $date
     * @param string $format
     * @return string
     */
    public static function format(DateTime $date, string $format): string
    {
        return $date->format($format);
    }

    /**
     * Returns current date & time in given format.
     * 
     * @param string $format
     * @return string   
     */
    public static function formatNow($format = null): string
    {
        return self::format(new DateTime(), $format === null ? self::MYSQL_DATETIME_FORMAT : $format);
    }

    /**
     * Returns given time in unix format.
     * 
     * @param DateTime $date
     * @return string
     */
    public static function unix(?DateTime $date = null): string
    {
        if ($date === null) {
            return self::formatNow('U');
        }
        return self::format($date, 'U');
    }

    /**
     * Returns given date in mysql datetime(Y-m-d H:i:s) format.
     * 
     * @param DateTime $date
     * @param string $format
     * @return string
     */
    public static function mysql(?DateTime $date = null): string
    {
        if ($date === null) {
            return self::formatNow(self::MYSQL_DATETIME_FORMAT);
        }
        return self::format($date, self::MYSQL_DATETIME_FORMAT);
    }

    /**
     * Returns given date in mysq date(Y-m-d) format.
     * 
     * @param DateTime $date
     * @return string
     */
    public static function mysqlDate(DateTime $date = null): string
    {
        if ($date === null) {
            return self::formatNow(self::MYSQL_DATE_FORMAT);
        }
        return self::format($date, self::MYSQL_DATE_FORMAT);
    }

    /**
     * Returns current time.
     * 
     * @return \DateTime
     */
    public static function getNow(): DateTIme
    {
        return (new DateTime());
    }

    /**
     * Returns times ago readable statement.
     * 
     * @param \DateTime $date
     * @param bool $full
     * @return type
     */
    public static function timeAgo(DateTime $date, bool $full = false): string
    {
        $now = new DateTime;
        $diff = $now->diff($date);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    /**
     * Returns TRUE if $date is current date.
     * 
     * @param DateTime $date
     * @return bool
     */
    public static function isToday(DateTime $date): bool
    {
        return $date->format('Ymd') === self::formatNow('Ymd');
    }

    /**
     * Returns TRUE if $date falls into current week.
     * 
     * @param DateTime $date
     * @return bool
     */
    public static function isCurrentWeek(DateTime $date): bool
    {
        return $date->format('WY') === self::formatNow('WY');
    }

    /**
     * Returns TRUE if $date falls into current month.
     * 
     * @param DateTime $date
     * @return bool
     */
    public static function isCurrentMonth(DateTime $date): bool
    {
        return $date->format('mY') === self::formatNow('mY');
    }

    /**
     * Returns TRUE if $date falls into current year.
     * 
     * @param DateTime $date
     * @return bool
     */
    public static function isCurrentYear(DateTime $date): bool
    {
        return $date->format('Y') === self::formatNow('Y');
    }

}
