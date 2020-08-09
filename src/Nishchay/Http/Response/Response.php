<?php

namespace Nishchay\Http\Response;

use Nishchay\Http\Response\Status;
use Nishchay\Http\Request\Request;
use Nishchay\Http\ContentTypeAlias;

/**
 *  HTTP Response class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Response
{

    /**
     * Short names of all content types.
     * 
     * @var     array 
     */
    private $contentTypeAlias = array(
        'html' => 'text/html',
        'plain' => 'text/plain',
        'text' => 'text/plain',
        'js' => 'application/javascript',
        'javascript' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'text/xml',
        'png' => 'image/png',
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
    );

    /**
     * Content type.
     * 
     * @var     string 
     */
    private $contentType;

    /**
     *
     * @var type 
     */
    private static $responseCode;

    /**
     * 
     * @param   string      $contentType
     */
    public function __construct($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Sets response content type.
     * 
     * @param   string    $contentType
     */
    public static function setContentType($contentType)
    {
        (new static($contentType))->handle();
    }

    /**
     * 
     * @return  NULL
     */
    private function handle()
    {
        return $this->set(ContentTypeAlias::getContentType($this->contentType));
    }

    /**
     * Sets header content type.
     * 
     * @param   string                                      $contentType
     * @throws  \Nishchay\Exception\ApplicationException
     */
    private function set($contentType)
    {
        static::setHeader('Content-Type', $contentType);
    }

    /**
     * Sets passed status code.
     * 
     * @param   int $code
     * @param   string $message
     * @return  string
     */
    public static function setStatus($code, $message = false)
    {
        # If heade status already been set, we will not set it again.
        if (static::$responseCode !== null) {
            return false;
        }
        static::$responseCode = $code;
        $message = static::getMessage($code, $message);
        if (in_array(substr(php_sapi_name(), 0, 3), ['cgi', 'fpm'])) {
            return static::setHeader('Status', "{$code} {$message}");
        }
        $protocol = Request::server('PROTOCOL') ?
                Request::server('PROTOCOL') : 'HTTP/1.0';
        return static::setHeader("{$protocol} {$code} {$message}");
    }

    /**
     * Returns status code.
     * 
     * @return int
     */
    public static function getStatusCode()
    {
        return static::$responseCode;
    }

    /**
     * Returns status message.
     * 
     * @param   int $code
     * @param   string $message
     * @return  string
     */
    private static function getMessage($code, $message)
    {
        return $message ? $message :
                (
                Status::isExist($code) ? Status::get($code) : ('Http Status ' . $code)
                );
    }

    /**
     * Sets header information.
     * 
     * @param string $name
     * @param string $value
     */
    public static function setHeader(string $name, string $value = '')
    {

        if (headers_sent()) {
            return false;
        }

        header($name . ($value ? (': ' . $value) : ''));
        return TRUE;
    }

}
