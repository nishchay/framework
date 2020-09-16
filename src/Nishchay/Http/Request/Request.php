<?php

namespace Nishchay\Http\Request;

use Nishchay;
use Processor;
use Nishchay\Controller\Forwarder;
use Nishchay\Utility\URI;
use Nishchay\Http\Request\RequestRedirector;
use Nishchay\Utility\Coding;
use Nishchay\Http\ContentTypeAlias;

/**
 * Request class to fetch GET, POST parameter, Uploaded files & server values.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Request
{

    /**
     * Name of property which have instance
     * of this class reference stored.
     */
    const INSTANCE_PROPERTY = 'instance';

    /**
     * GET method name.
     * 
     */
    const GET = 'GET';

    /**
     * Post method name.
     * 
     */
    const POST = 'POST';

    /**
     * Put method name.
     */
    const PUT = 'PUT';

    /**
     * Delete method name.
     */
    const DELETE = 'DELETE';

    /**
     * Patch method name.
     */
    const PATCH = 'PATCH';

    /**
     * Header name.
     * 
     */
    const HEADER = 'HEADER';

    /**
     * All GET Request Parameters
     * 
     * @var array 
     */
    private $_GET;

    /**
     * All POST Request Parameter
     * 
     * @var array 
     */
    private $_POST;

    /**
     * PHP's $_SERVER array is assigned to this variable
     * 
     * @var array 
     */
    private $_SERVER;

    /**
     * Server Information.
     * Software name, Server name, IP Address, 
     * Port Number being used this server, Signature and admin of this server
     *  
     * @var array
     */
    private $server = array(
        'SOFTWARE' => 'SERVER_SOFTWARE',
        'NAME' => 'SERVER_NAME',
        'IP' => 'REMOTE_ADDR',
        'PORT' => 'REMOTE_PORT',
        'SERVER_IP' => 'SERVER_ADDR',
        'SERVER_PORT' => 'SERVER_PORT',
        'SIGNATURE' => 'SERVER_SIGNATURE',
        'ADMIN' => 'SERVER_ADMIN',
        'HOST' => 'HTTP_HOST',
        'AGENT' => 'HTTP_USER_AGENT',
        'ACCEPT' => 'HTTP_ACCEPT',
        'LANGUAGE' => 'HTTP_ACCEPT_LANGUAGE',
        'ENCODING' => 'HTTP_ACCEPT_ENCODING',
        'CONNECTION' => 'HTTP_CONNECTION',
        'QUERY' => 'QUERY_STRING',
        'METHOD' => 'REQUEST_METHOD',
        'SCHEME' => 'REQUEST_SCHEME',
        'URI' => 'REQUEST_URI',
        'SCRIPT' => 'SCRIPT_NAME',
        'PROTOCOL' => 'SERVER_PROTOCOL',
        'SELF' => 'PHP_SELF'
    );

    /**
     * Instance of this class.
     * 
     * @var \Nishchay\Http\Request 
     */
    private static $instance = null;

    /**
     * Returns instance.
     * 
     * @return self
     */
    private static function getInstance()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new static();
        self::init();
        return self::$instance;
    }

    /**
     * Forwards request to another route within server.
     * 
     * @param \Nishchay\Http\Request\RequestForwarder $request_forwarder
     */
    public static function forward(RequestForwarder $request_forwarder)
    {

        return new Forwarder($request_forwarder);
    }

    /**
     * Returns get parameter value.
     * 
     * @param   string          $name
     * @return  string|array
     */
    public static function get($name = null)
    {
        return self::getInstance()->getArrayValue('_GET', $name);
    }

    /**
     * Returns segment value.
     * If segment does not exist in url it returns false.
     * 
     * @param type $name
     * @return boolean
     */
    public static function segment($name = null)
    {
        $segment = Processor::getStageDetail('segment');

        if ($name === null) {
            return $segment;
        }

        if (array_key_exists($name, $segment)) {
            return $segment[$name];
        }

        return false;
    }

    /**
     * Common method which used to get value of array.
     * $type can be any of $_GET,$_POST,$client or $server.
     * 
     * @param   string              $type
     * @param   string              $name
     * @return  boolean|string
     */
    private function getArrayValue($type, $name = null)
    {
        # If name is null, return whole array.
        if ($name == null) {
            return self::getInstance()->{$type};
        }

        $method = self::getInstance()->{$type};

        if (array_key_exists($name, $method)) {
            return $method[$name];
        }

        return FALSE;
    }

    /**
     * Returns url following with base url.
     * 
     * @param   string      $following
     * @return  string
     */
    public static function getBaseURL($following = '')
    {
        return URI::getBaseURL($following);
    }

    /**
     * Returns server defined values.
     * 
     * @param   string          $type
     * @param   string          $name
     * @return  boolean|string
     */
    private function getServerValue($type, $name)
    {
        # Making server value retrivable via its alias name.
        $serverKey = self::getInstance()
                ->getArrayValue($type, strtoupper($name));

        # Above returns false if $name does not found in $type
        # If it does not returns false we will use server key instead of
        # $name passed in.
        if ($serverKey !== false) {
            $name = $serverKey;
        }
        if (array_key_exists($name, self::getInstance()->_SERVER)) {
            return self::getInstance()->_SERVER[$name];
        }
        return false;
    }

    /**
     * Assigns $_GET,$_POST,$_SERVER to class property. 
     */
    private static function init()
    {
        self::$instance->_SERVER = $_SERVER;
        self::$instance->_GET = $_GET;
        self::$instance->_POST = self::getInstance()->getPostParams();

        if (Nishchay::isApplicationRunningForCommand()) {
            self::$instance->_SERVER['REQUEST_METHOD'] = self::GET;
        }
    }

    /**
     * Parses post params based on request content type.
     * 
     * @return array
     */
    private function getPostParams()
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $contentType = self::getContentType();
        if (($pos = strpos($contentType, ';')) !== false) {
            $contentType = substr($contentType, 0, $pos);
        }
        if ($contentType === ContentTypeAlias::getContentType('json')) {
            $post = Coding::decodeJSON(self::getInput(), true);
            return is_array($post) ? $post : [];
        } else {
            mb_parse_str(self::getInput(), $post);
            return $post;
        }
    }

    /**
     * Returns post parameter value.
     * 
     * @param   string          $name
     * @return  string|array
     */
    public static function post($name = null)
    {
        return self::getInstance()->getArrayValue('_POST', $name);
    }

    /**
     * Redirects to another request.
     * 
     * @param string $redirect
     */
    public static function redirect($redirect)
    {
        header('Location: ' . $redirect);
    }

    /**
     * Returns RequestRedirector instance with given route within application.
     * 
     * @param string $route
     * @return RequestRedirector
     */
    public static function getRedirectWithin($route)
    {
        return static::getRedirect(static::getBaseURL($route));
    }

    /**
     * Returns RequestRedirector instance for outside application.
     * 
     * @param string $url
     * @return RequestRedirector
     */
    public static function getRedirect($url)
    {
        return new RequestRedirector($url);
    }

    /**
     * Returns Sever Specific information which shown below
     * Software name, Server name, IP Address, 
     * Port Number being used this server, Signature and admin of this server
     * 
     * @param   string      $name
     * @return  string
     */
    public static function server($name = null)
    {
        if ($name == null) {
            return self::getInstance()->_SERVER;
        } else {
            return self::getInstance()->getServerValue('server', $name);
        }
    }

    /**
     * Returns TRUE if current request is AJAX.
     * 
     * @return type
     */
    public static function isAJAX()
    {
        $with = self::server('HTTP_X_REQUESTED_WITH');
        return $with && strtolower($with) == 'xmlhttprequest';
    }

    /**
     * Returns TRUE if request is GET.
     * 
     * @return boolean
     */
    public static function isGet()
    {
        return static::server('METHOD') === static::GET;
    }

    /**
     * Returns TRUE if request is POST.
     * 
     * @return boolean
     */
    public static function isPost()
    {
        return static::server('METHOD') === static::POST;
    }

    /**
     * Returns TRUE if request is PUT.
     * 
     * @return boolean
     */
    public static function isPut()
    {
        return static::server('METHOD') === static::PUT;
    }

    /**
     * Returns TRUE if request is DELETE.
     * 
     * @return boolean
     */
    public static function isDelete()
    {
        return static::server('METHOD') === static::DELETE;
    }

    /**
     * Returns TRUE if request is PATCH.
     * 
     * @return boolean
     */
    public static function isPatch()
    {
        return static::server('METHOD') === static::PATCH;
    }

    /**
     * Returns Raw input data.
     * 
     * @return string
     */
    public static function getInput()
    {
        return file_get_contents('php://input');
    }

    /**
     * Returns request content type.
     */
    public static function getContentType()
    {
        return self::server('HTTP_CONTENT_TYPE');
    }

    /**
     * Returns uploaded file detail.
     * 
     * @param type $name
     * @return boolean|\Nishchay\Http\Request\RequestFile
     */
    public static function file($name)
    {
        if (array_key_exists($name, $_FILES) === false) {
            return false;
        }
        $files = $_FILES[$name];

        if (empty($files['name'])) {
            return false;
        }

        if (is_string($files['name'])) {
            return new RequestFile(... array_values($files));
        }

        $array = [];
        for ($i = 0; $i < count($files['name']); $i++) {
            $array[] = new RequestFile(
                    $files['name'][$i],
                    $files['type'][$i],
                    $files['tmp_name'][$i],
                    $files['error'][$i],
                    $files['size'][$i]
            );
        }

        return $array;
    }

    /**
     * Returns IP.
     * Also considers proxy setting as declared in application.php setting file.
     * 
     * @return string
     */
    public static function ip()
    {
        $ip = self::server('IP');
        if (Nishchay::getConfig('proxy.active') !== true) {
            return $ip;
        }

        $header = Nishchay::getConfig('proxy.header');

        if (empty($header)) {
            return $ip;
        }

        $header = strtoupper($header);
        $header = strpos($header, 'HTTP') === 0 ? $header : ('HTTP_' . $header);

        $header = str_replace('-', '_', $header);

        $ips = self::server($header);

        if (empty($ips)) {
            return $ip;
        }

        $proxyIPs = Nishchay::getConfig('proxy.header');
        $proxyIPs = is_array($proxyIPs) === false ? [] : $proxyIPs;

        $ips = array_map('trim', $ips);
        $ips = array_diff($ips, $proxyIPs);

        if (empty($ips)) {
            return $ip;
        }

        return array_pop($ips);
    }

}
