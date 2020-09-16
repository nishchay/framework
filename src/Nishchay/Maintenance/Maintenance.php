<?php

namespace Nishchay\Maintenance;

use DateTime;
use Nishchay;
use Processor;
use Nishchay\Utility\DateUtility;
use Nishchay\Utility\MethodInvokerTrait;
use Nishchay\Utility\Coding;

/**
 * Maintenance class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Maintenance
{

    use MethodInvokerTrait;

    /**
     * Maintenance name.
     */
    const MAINTENANCE = 'maintenance';

    /**
     * Maintenance types to check.
     * 
     * @var array 
     */
    private $checks = ['timed', 'agent', 'callback'];

    /**
     * Allowed types.
     * 
     * @var array 
     */
    private $allowedCheck = ['context', 'scope', 'route'];

    /**
     * Maintenance mode which matched for current route.
     * 
     * @var string 
     */
    private $mode;

    /**
     * Maintenance reason.
     * 
     * @var srting 
     */
    private $reason;

    /**
     * true if current route is allowed for matched maintenance mode.
     * 
     * @var boolean 
     */
    private $allowed = false;

    /**
     * Located maintenance route.
     * 
     * @var type 
     */
    private $maintenanceRoute;

    /**
     * Returns route if current request is in maintenance mode.
     * 
     * @return boolean|string
     */
    public function getRoute()
    {

        # No need to check again if we already have found maintenance route
        # for current request.
        if ($this->maintenanceRoute !== null) {
            return $this->maintenanceRoute;
        }

        $isAnyActive = false;
        foreach ($this->checks as $checkName) {
            $config = $this->getConfig($checkName);
            if (isset($config->active) && $config->active === true) {
                $isAnyActive = true;
                $this->maintenanceRoute = $this->execute($checkName, $config);
                if ($this->maintenanceRoute !== false) {
                    $this->mode = $checkName;
                    return $this->isItAllowed($this->maintenanceRoute);
                }
            }
        }

        # Returning route name if all type of mode is inactive.
        if ($isAnyActive === false) {
            return $this->getConfig('route');
        }
        return false;
    }

    /**
     * Checks if current route is allowed even in maintenance mode.
     * 
     * @param string $route
     * @return boolean|string
     */
    private function isItAllowed($route)
    {

        if ($this->getConfig('ignoreAllowed') === true) {
            return $route;
        }

        $allowed = $this->getConfig('allowed');
        foreach ($this->allowedCheck as $name) {
            if (!isset($allowed->{$name})) {
                continue;
            }
            $config = $allowed->{$name};
            if (isset($config->active) && $config->active === true) {
                if ($this->execute('Allowed' . ucfirst($name), $config)) {
                    $this->allowed = $name;
                    return false;
                }
            }
        }

        return $route;
    }

    /**
     * Checks if context of current request is in allowed context list.
     *  
     * @param \stdClass $config Allowed context list.
     * @return boolean  Returns true if context of current request is in
     *                  allowed context list.
     */
    private function executeAllowedContext($config)
    {
        if ($this->isThereNoList($config)) {
            return false;
        }

        return in_array(Processor::getStageDetail('context'), $config->list);
    }

    /**
     * Checks if scope of current processing request is allowed.
     * 
     * @param type $config
     * @return boolean
     */
    private function executeAllowedScope($config)
    {
        if ($this->isThereNoList($config)) {
            return false;
        }
        $scopeName = Processor::getStageDetail('scopeName');
        if ($scopeName === false) {
            return false;
        }
        return in_array($scopeName, $config->list);
    }

    /**
     * Checks if current request is in allowed route.
     * 
     * @param \stdClass $config Allowed route list.
     * @return boolean  Returns true if current request is in allowed route.
     */
    private function executeAllowedRoute($config)
    {
        if ($this->isThereNoList($config)) {
            return false;
        }

        $urlString = Processor::getStageDetail('urlString');

        foreach ($config->list as $allowed) {

            # We need both type and match in $allowed.
            if (!isset($allowed->type) && !isset($allowed->match)) {
                continue;
            }

            if ($this->matchURL($urlString, $allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns url is matched.
     * 
     * @param string $url
     * @param \stdClass $config
     * @return boolean
     */
    private function matchURL($url, $config)
    {
        switch ($config->type) {
            case 'fixed':
                if ($url === $config->match) {
                    return true;
                }
                break;
            case 'regex':
                if (preg_match("#{$config->match}#", $url)) {
                    return true;
                }
                break;
            default:
                break;
        }
        return false;
    }

    /**
     * 
     * @param type $checkName
     * @param type $config
     * @return type
     */
    private function execute($checkName, $config)
    {
        return $this->invokeMethod([$this, 'execute' . ucfirst($checkName)], [$config]);
    }

    /**
     * Returns config of given check name.
     * 
     * @param string $name
     * @return \stdClass
     */
    private function getConfig($name)
    {
        return Nishchay::getSetting(self::MAINTENANCE . '.' . $name);
    }

    /**
     * Executes callback for maintenance check.
     * 
     * @param \stdClass $config
     * @return boolean
     */
    private function executeCallback($config)
    {

        if ($this->isThereNoList($config)) {
            return false;
        }

        $default = isset($config->default) ? $config->default : false;
        foreach ($config->list as $item) {

            # $item can contain only callback name only as a string.
            $callback = is_array($item) ? $item[0] : $item;

            if ($this->invokeMethod($callback) !== false) {
                is_array($item) && $this->setReason($item);

                # Returning default route if $item does not has its own route
                # or will return route mentioned in $item.
                return (is_array($item) && isset($item[1])) ?
                        $item[1] : $default;
            }
        }
        return false;
    }

    /**
     * Sets reason of maintenance.
     * 
     * @param type $item
     */
    private function setReason($item)
    {
        $this->reason = isset($item[2]) ? $item[2] : false;
    }

    /**
     * Executes time slots maintenance check.
     * 
     * @param \stdClass $config
     * @return boolean
     */
    private function executeTimed($config)
    {
        if ($this->isThereNoList($config)) {
            return false;
        }

        $now = DateUtility::getNow();

        $route = isset($config->default) ? $config->default : false;
        foreach ($config->list as $timed) {
            $slot = current($timed);

            # $slot should contain start and end time.
            if (!isset($slot[0]) || !isset($slot[1])) {
                continue;
            }


            # This will returns false $slot time is in invalid format.
            $start = $this->getTime($slot[0]);
            $end = $this->getTime($slot[1]);

            # No need proceed if any of start and end time is false.
            if ($start === false || $end === false) {
                continue;
            }

            if ($now >= $start && $now <= $end) {
                $this->setReason($timed);
                return isset($timed[1]) ? $timed[1] : $route;
            }
        }

        return false;
    }

    /**
     * Returns Datetime instance of given $time.
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

    /**
     * Executes agent check for maintenance.
     * 
     * @param \stdClass $config
     * @return boolean
     */
    private function executeAgent($config)
    {
        if ($this->isThereNoList($config)) {
            return false;
        }

        # Finding browser name and its version from current request's user
        # agent. Making browser to lower case to make comparison case
        # insensitive.
        $browser = get_browser();
        $name = strtolower($browser->browser);
        $version = $browser->version;

        # In the case of invert, if there's match in list we should not put
        # application into maintenance mode. Because this was matched it means
        # application is allowed for matched agent. we will directly return
        # false indicating there's no maintenance mode.
        $invert = isset($config->invert) ? $config->invert : false;
        $default = isset($config->default) ? $config->default : false;

        foreach ($config->list as $item) {
            $item = is_array($item) ? $item : [$item];
            list($agentName) = $item;

            # By default making version matched to true if there is only agent
            # name exist. This way we will make all version to match for agent.
            $versionMatch = true;

            # Agent can be in {$agentName}/{$agentVersion} format.
            if (strpos($agentName, '/')) {
                list($agentName, $agentVersion) = explode('/', $agentName);

                # Checking version with the range.
                $versionMatch = $this->checkVersion(explode('-', $agentVersion), $version);
            }


            if (strtolower($agentName) === $name && $versionMatch) {
                $this->setReason($item);

                # There's match and invert is true means will allow that agent.
                # We are returning false to indicate that there's no maintenance mode.
                return $invert ? false :
                        ($item[1] ?? $default);
            }
        }

        # Nothing matched so invert = true then return default route.
        return $invert ? $default : false;
    }

    /**
     * Checks version with range.
     * 
     * @param array $range
     * @param string $version
     * @return boolean
     */
    private function checkVersion($range, $version)
    {
        if (count($range) === 2) {
            return Coding::isVersionMatch($range, $version);
        }

        return $version == $range[0];
    }

    /**
     * Returns true if its there list config and is array or instance
     * of stdClass.
     * 
     * @param \stdClass $config
     * @return boolean
     */
    private function isThereNoList($config)
    {
        return !isset($config->list) || !is_array($config->list);
    }

    /**
     * Returns maintenance mode which was matched.
     * 
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Returns maintenance reason.
     * 
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Returns true if maintenance mode matched but request is allowed even in
     * maintenance mode.
     * 
     * @return boolean
     */
    public function isAllowed()
    {
        return $this->allowed;
    }

}
