<?php

namespace Nishchay\Http\Response;

/**
 * HTTP status codes.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Status
{

    const CODE_100 = 'Continue';
    const CODE_101 = 'Switching Protocols';
    const CODE_102 = 'Processing (WebDAV)';
    const CODE_200 = 'OK';
    const CODE_201 = 'Created';
    const CODE_202 = 'Accepted';
    const CODE_203 = 'Non-Authoritative Information';
    const CODE_204 = 'No Content';
    const CODE_205 = 'Reset Content';
    const CODE_206 = 'Partial Content';
    const CODE_207 = 'Multi-Status (WebDAV)';
    const CODE_208 = 'Already Reported (WebDAV)';
    const CODE_300 = 'Multiple Choices';
    const CODE_301 = 'Moved Permanently';
    const CODE_302 = 'Found';
    const CODE_303 = 'See Other';
    const CODE_304 = 'Not Modified';
    const CODE_305 = 'Use Proxy';
    const CODE_306 = '(Unused)';
    const CODE_307 = 'Temporary Redirect';
    const CODE_308 = 'Permanent Redirect (experimental)';
    const CODE_400 = 'Bad Request';
    const CODE_401 = 'Unauthorized';
    const CODE_402 = 'Payment Required';
    const CODE_403 = 'Forbidden';
    const CODE_404 = 'Not Found';
    const CODE_405 = 'Method Not Allowed';
    const CODE_406 = 'Not Acceptable';
    const CODE_407 = 'Proxy Authentication Required';
    const CODE_408 = 'Request Timeout';
    const CODE_409 = 'Conflict';
    const CODE_410 = 'Gone';
    const CODE_411 = 'Length Required';
    const CODE_412 = 'Precondition Failed';
    const CODE_413 = 'Request Entity Too Large';
    const CODE_414 = 'Request-URI Too Long';
    const CODE_415 = 'Unsupported Media Type';
    const CODE_416 = 'Requested Range Not Satisfiable';
    const CODE_417 = 'Expectation Failed';
    const CODE_418 = 'I\'m a teapot (RFC 2324)';
    const CODE_420 = 'Enhance Your Calm (Twitter)';
    const CODE_422 = 'Unprocessable Entity (WebDAV)';
    const CODE_423 = 'Locked (WebDAV)';
    const CODE_424 = 'Failed Dependency (WebDAV)';
    const CODE_425 = 'Reserved for WebDAV';
    const CODE_426 = 'Upgrade Required';
    const CODE_428 = 'Precondition Required';
    const CODE_429 = 'Too Many Requests';
    const CODE_431 = 'Request Header Fields Too Large';
    const CODE_444 = 'No Response (Nginx)';
    const CODE_449 = 'Retry With (Microsoft)';
    const CODE_450 = 'Blocked by Windows Parental Controls (Microsoft)';
    const CODE_451 = 'Unavailable For Legal Reasons';
    const CODE_499 = 'Client Closed Request (Nginx)';
    const CODE_500 = 'Internal Server Error';
    const CODE_501 = 'Not Implemented';
    const CODE_502 = 'Bad Gateway';
    const CODE_503 = 'Service Unavailable';
    const CODE_504 = 'Gateway Timeout';
    const CODE_505 = 'HTTP Version Not Supported';
    const CODE_506 = 'Variant Also Negotiates (Experimental)';
    const CODE_507 = 'Insufficient Storage (WebDAV)';
    const CODE_508 = 'Loop Detected (WebDAV)';
    const CODE_509 = 'Bandwidth Limit Exceeded (Apache)';
    const CODE_510 = 'Not Extended';
    const CODE_511 = 'Network Authentication Required';
    const CODE_598 = 'Network read timeout error';
    const CODE_599 = 'Network connect timeout error';

    /**
     * Returns true if given HTTP status code exist in this class.
     * 
     * @param int $code
     * @return boolean
     */
    public static function isExist($code)
    {
        return defined(__CLASS__ . '::CODE_' . $code);
    }

    /**
     * Returns HTTP status code description.
     * 
     * @param int $code
     * @return string
     */
    public static function get($code)
    {
        if (self::isExist($code))
        {
            return constant(__CLASS__ . '::CODE_' . $code);
        }
        return FALSE;
    }

}
