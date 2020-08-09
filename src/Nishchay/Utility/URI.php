<?php

namespace Nishchay\Utility;

use Nishchay;
use Processor;
use Nishchay\Http\Request\Request;

/**
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class URI
{

    /**
     * Base URL of application.
     * 
     * @var string 
     */
    private static $baseUrl = null;

    /**
     * Returns Base URL.
     * 
     * @return string  
     */
    public static function getBaseURL($following = '')
    {
        if (strlen(Nishchay::getConfig('config.baseUrl')) > 0) {
            $baseUrl = trim(Nishchay::getConfig('config.baseUrl'), '/') . '/';
        } else {
            if (self::$baseUrl === null) {

                $domain = Request::server('HOST');
                $script = Request::server('SCRIPT');
                $sub = str_replace(basename($script), '', $script);
                self::$baseUrl = $baseUrl = self::getProtocol() . "://" .
                        $domain . $sub;
            } else {
                $baseUrl = self::$baseUrl;
            }
        }

        if ($following != "") {
            $baseUrl .= $following;
        }

        return $baseUrl;
    }

    /**
     * Returns protocol.
     * 
     * @return string
     */
    private static function getProtocol()
    {
        $protocol = Request::server('HTTPS');
        if ($protocol && (strtolower($protocol) == 'https' || strtolower($protocol) === 'on')) {
            return 'https';
        }
        return 'http';
    }

    /**
     * Return current URL of the request.
     * 
     * @return string
     */
    public static function getCurrentURL()
    {
        return static::getBaseURL(Processor::getStageDetail('urlString'));
    }

}
