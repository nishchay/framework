<?php

namespace Nishchay\Route\Pattern;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\ApplicationException;

/**
 * Abstract pattern.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.1
 * @author      Bhavik Patel
 */
abstract class AbstractPattern
{

    /**
     * Route annotation name.
     */
    const ROUTE_NAME = 'route';

    /**
     * Named scope annotation name.
     */
    const NAMEDSCOPE_NAME = 'namedscope';

    /**
     * Service annotation name.
     */
    const SERVICE_NAME = 'service';

    /**
     * Response annotation name.
     */
    const RESPONSE_NAME = 'response';

    /**
     * Pattern name.
     * 
     * @var type 
     */
    protected $patternName;

    /**
     * Settings for route annotation.
     * 
     * @var bool|null 
     */
    protected $route;

    /**
     * Settings for route annotation.
     * 
     * @var bool|null 
     */
    protected $namedscope;

    /**
     * Settings for route annotation.
     * 
     * @var bool|null 
     */
    protected $service;

    /**
     * Settings for route annotation.
     * 
     * @var bool|null 
     */
    protected $response;

    /**
     * Settings for route annotation.
     * 
     * @var bool|null 
     */
    protected $annotations;

    /**
     * 
     * @param type $name
     */
    public function __construct($name)
    {
        $this->setPatternName($name);
    }

    /**
     * Processes route pattern config.
     * 
     * @param array $annotations
     * @return $this
     */
    public function processConfig($annotations)
    {
        $this->setAnnotations($annotations);
        return $this->processRoute()
                        ->processService()
                        ->processNamedScope()
                        ->processResponse();
    }

    /**
     * Checks required validation for the annotation.
     * 
     * @param string $name
     * @return null
     */
    protected function checkRequired($name)
    {
        if ($this->{$name} === true && $this->isSet($name) === false) {
            throw new NotSupportedException('Route pattern [' . $this->patternName . '] requires [' . $name . '] annotation to be defined on method.', null, null, 926014);
        }

        return $this;
    }

    /**
     * Checks override validation for the annotation.
     * 
     * @param type $name
     * @throws ApplicationException
     */
    protected function checkOverride($name)
    {
        $config = $this->{$name};
        if (($config === false || (isset($config['override']) && $config['override'] === false)) && $this->isSet($name)) {
            throw new ApplicationException('Route pattern [' . $this->patternName . '] disallow [' . $name . '] annotation to be defined on method.', null, null, 926015);
        }
    }

    /**
     * Returns TRUE if given annotation exists.
     * 
     * @param string $name
     * @return bool
     */
    protected function isSet(string $name): bool
    {
        return array_key_exists($name, $this->annotations);
    }

    /**
     * Checks route setting.
     * 
     * @return $this
     * @throws NotSupportedException
     * @throws ApplicationException
     */
    public function processRoute()
    {
        $this->checkRequired(self::ROUTE_NAME);

        return $this;
    }

    /**
     * Checks settings for named scope.
     * 
     * @return $this
     */
    public function processNamedScope()
    {
        $this->checkRequired(self::NAMEDSCOPE_NAME)
                ->checkOverride(self::NAMEDSCOPE_NAME);

        $namedScopes = $this->getNamedscope();
        if (is_array($namedScopes)) {
            $this->annotations[self::NAMEDSCOPE_NAME]['name'] = array_merge($namedScopes['name'] ?? $namedScopes, $this->annotations[self::NAMEDSCOPE_NAME] ?? []);
        }

        return $this;
    }

    /**
     * Checks settings for service.
     * 
     * @return $this
     */
    public function processService()
    {
        $this->checkRequired(self::SERVICE_NAME)
                ->checkOverride(self::SERVICE_NAME);

        if (is_string($this->service)) {
            $this->annotations[self::SERVICE_NAME] = array_merge([], $this->annotations[self::NAMEDSCOPE_NAME] ?? []);
        }

        return $this;
    }

    /**
     * Checks settings for service.
     * 
     * @return $this
     */
    public function processResponse()
    {
        $this->checkRequired(self::RESPONSE_NAME)
                ->checkOverride(self::RESPONSE_NAME);

        if (is_array($this->response)) {
            $this->annotations[self::RESPONSE_NAME] = array_merge(['type' => $this->response['type']], $this->annotations[self::RESPONSE_NAME] ?? []);
        }

        return $this;
    }

    /**
     * Returns pattern name.
     * 
     * @return string
     */
    public function getPatternName()
    {
        return $this->patternName;
    }

    /**
     * Returns settings for route annotation.
     * 
     * @return type
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Returns settings for named scope annotation.
     * 
     * @return mixed
     */
    public function getNamedscope()
    {
        return $this->namedscope;
    }

    /**
     * Returns settings for service annotation.
     * 
     * @return bool|null
     */
    public function getService(): ?bool
    {
        return $this->service;
    }

    /**
     * Returns settings for service annotation.
     * 
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns annotations.
     * 
     * @return mixed
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * Sets pattern name.
     * 
     * @param type $patternName
     * @return $this
     */
    public function setPatternName($patternName)
    {
        $this->patternName = $patternName;
        return $this;
    }

    /**
     * Sets route annotation setting.
     * 
     * @param type $route
     * @return $this
     */
    public function setRoute($route)
    {
        if ($route !== true && $route !== null) {
            throw new ApplicationException('Invalid value for [' . self::ROUTE_NAME . '] setting in pattern [' . $this->patternName . '].', null, null, 926016);
        }
        $this->route = $route;
        return $this;
    }

    /**
     * Sets named scope annotation setting.
     * 
     * @param type $namedscope
     * @return $this
     */
    public function setNamedscope($namedscope)
    {
        if (is_array($namedscope) && isset($namedscope['name'])) {
            $namedscope['name'] = (array) $namedscope['name'];
        }
        $this->namedscope = $namedscope;
        return $this;
    }

    /**
     * Sets service annotation setting.
     * 
     * @param type $service
     * @return $this
     */
    public function setService($service)
    {
        if (is_bool($service) === false && $service !== null && strtolower($service) !== 'all') {
            throw new ApplicationException('Invalid value for [' . self::SERVICE_NAME . '] setting in pattern [' . $this->patternName . '].', null, null, 926017);
        }
        $this->service = $service;
        return $this;
    }

    /**
     * Sets response annotation setting.
     * 
     * @param type $response
     * @return $this
     */
    public function setResponse($response)
    {
        if (is_array($response)) {
            $response['type'] = (string) $response['type'];
        } else if (is_string($response)) {
            $response = ['type' => $response];
        }

        $this->response = $response;
        return $this;
    }

    /**
     * Sets annotation.
     * 
     * @param type $annotation
     * @return $this
     */
    public function setAnnotations($annotation)
    {
        $this->annotations = $annotation;
        return $this;
    }

    abstract public function processMethod(string $class, string $method);
}
