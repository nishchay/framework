<?php

namespace Nishchay\Route;

use Nishchay;
use Nishchay\Route\Annotation\Route;
use Nishchay\Utility\DateUtility;
use Nishchay\Utility\Coding;
use Nishchay\Processor\AbstractSingleton;
use Nishchay\Http\Request\Request;

/**
 * Route visibility checker class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Visibility extends AbstractSingleton
{

    /**
     * Visibility config.
     * 
     * @var type 
     */
    private $setting;

    /**
     * Instance of this class.
     * 
     * @var self
     */
    protected static $instance;

    /**
     * Load visibility setting.
     */
    protected function onCreateInstance()
    {
        $this->setting = Nishchay::getSetting('routes.visibility');
    }

    /**
     * Returns instance route method annotation.
     * 
     * @param Route $route
     * @return \Nishchay\Controller\Annotation\Method\Method
     */
    private function getMethod(Route $route)
    {
        return Nishchay::getControllerCollection()->getMethod($route->getClass() . '::' . $route->getMethod());
    }

    /**
     * Checks route visibility.
     * 
     * @param Route $route
     * @return boolean
     */
    public function check(Route $route): bool
    {
        # Visibility is disabled.
        if (($this->setting->active ?? false) === false) {
            return true;
        }

        $config = $this->setting->config ?? false;

        # There's no visibility config
        if (is_array($config) === false || empty($config)) {
            return true;
        }

        $method = $this->getMethod($route);

        # Let's iterate over and find visibility if any.
        foreach ($config as $row) {
            if (($row->active ?? false) === true) {
                if (!isset($row->eligible)) {
                    continue;
                }

                if ($method->getNamedscope() !== false && is_array($row->eligible->scope ?? false)) {
                    if (!empty(array_intersect($method->getNamedscope()->getName(), $row->eligible->scope))) {
                        return $this->checkVisibility($row->visible ?? false);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Checks visibility by time, agent and IP.
     * 
     * @param stdClass $visible
     * @return boolean
     */
    private function checkVisibility($visible): bool
    {
        # Visibility is disabled.
        if ($visible === false) {
            return false;
        }

        foreach ($visible as $row) {

            # By default we will make all true, this way if one or more config
            # not set it will be considered as true.
            $isTimeMatched = $isAgentMatched = $isIPMatched = $isCallbackSuccess = true;

            # Visible based on time.
            if (isset($row->time) && is_array($row->time)) {
                if ($this->checkTimeVisibility($row->time) !== true) {
                    $isTimeMatched = false;
                }
            }

            # Visible based on agent.
            if (isset($row->agent) && is_array($row->agent)) {
                if ($this->checkAgentVisibility($row->agent) !== true) {
                    $isAgentMatched = false;
                }
            }

            # Visible based on IP
            if (isset($row->ip) && is_array($row->ip)) {
                if (in_array(Request::ip(), $row->ip) !== true) {
                    $isIPMatched = false;
                }
            }

            # Visible based on callback
            if (isset($row->callback) && $row->callback instanceof \Closure) {
                if (call_user_func($row->callback) !== true) {
                    $isCallbackSuccess = false;
                }
            }

            if ($isTimeMatched && $isAgentMatched && $isIPMatched && $isCallbackSuccess) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks time slot.
     * 
     * @param type $times
     * @return boolean
     */
    private function checkTimeVisibility($times): bool
    {
        $now = DateUtility::getNow();
        foreach ($times as $slot) {
            if (count($slot) !== 2) {
                continue;
            }

            $start = $this->getTime($slot[0]);
            $end = $this->getTime($slot[1]);

            # Visible after given time
            if ($start && $end === false && $now >= $start) {
                return true;
            }

            # Visible before given time
            if ($start === false && $end && $now <= $end) {
                return true;
            }

            # Visible within given time
            if ($now >= $start && $now <= $end) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks agent visibility.
     * 
     * @param type $agents
     * @return boolean
     */
    private function checkAgentVisibility($agents): bool
    {
        $browser = get_browser();
        $name = strtolower($browser->browser);
        $version = $browser->version;

        foreach ($agents as $agentName) {
            $isVersionMatch = true;

            # Agent can be in {$agentName}/{$agentVersion} format.
            if (strpos($agentName, '/')) {
                list($agentName, $agentVersion) = explode('/', $agentName);

                # Checking version with the range.
                $isVersionMatch = $this->checkVersion(explode('-', $agentVersion), $version);
            }


            if (strtolower($agentName) === $name && $isVersionMatch) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks version with range.
     * 
     * @param array $range
     * @param string $version
     * @return boolean
     */
    private function checkVersion($range, $version): bool
    {
        if (count($range) === 2) {
            return Coding::isVersionMatch($range, $version);
        }

        return $version == $range[0];
    }

    /**
     * Returns DateTime instance of given $time.
     * 
     * @param string $time
     * @return \DateTime|boolean
     */
    private function getTime($time)
    {
        if (($date = DateUtility::createFromFormat('H:i', $time)) === false) {
            return DateUtility::createFromFormat('Y-m-d H:i', $time);
        }
        return $date;
    }

}
