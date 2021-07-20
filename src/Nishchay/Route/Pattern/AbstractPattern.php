<?php

namespace Nishchay\Route\Pattern;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\ApplicationException;
use Nishchay\Attributes\Controller\Method\{
    Service,
    Response,
    NamedScope
};

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
     * Route attribute name.
     */
    const ROUTE_NAME = 'route';

    /**
     * Named scope attribute name.
     */
    const NAMEDSCOPE_NAME = 'namedscope';

    /**
     * Service attribute name.
     */
    const SERVICE_NAME = 'service';

    /**
     * Response attribute name.
     */
    const RESPONSE_NAME = 'response';

    /**
     * Pattern name.
     * 
     * @var type 
     */
    protected $patternName;

    /**
     * Settings for route attribute.
     * 
     * @var bool|null 
     */
    protected $route;

    /**
     * Settings for route attribute.
     * 
     * @var bool|null 
     */
    protected $namedscope;

    /**
     * Settings for route attribute.
     * 
     * @var bool|null 
     */
    protected $service;

    /**
     * Settings for route attribute.
     * 
     * @var bool|null 
     */
    protected $response;

    /**
     * Settings for route attribute.
     * 
     * @var bool|null 
     */
    protected $attributes;

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
     * @param array $attribute
     * @return $this
     */
    public function processConfig($attribute)
    {
        $this->setAttributes($attribute);
        return $this->processRoute()
                        ->processService()
                        ->processNamedScope()
                        ->processResponse();
    }

    /**
     * Checks required validation for the attribute.
     * 
     * @param string $name
     * @return null
     */
    protected function checkRequired($name)
    {
        if ($this->{$name} === true && $this->isSet($name) === false) {
            throw new NotSupportedException('Route pattern [' . $this->patternName . '] requires [' . $name . '] attribute to be defined on method.',
                            null, null, 926014);
        }

        return $this;
    }

    /**
     * Checks override validation for the attribute.
     * 
     * @param type $name
     * @throws ApplicationException
     */
    protected function checkOverride($name)
    {
        $config = $this->{$name};
        if (($config === false || (isset($config['override']) && $config['override'] === false)) && $this->isSet($name)) {
            throw new ApplicationException('Route pattern [' . $this->patternName . '] disallow [' . $name . '] attribute to be defined on method.',
                            null, null, 926015);
        }
    }

    /**
     * Returns TRUE if given attribute exists.
     * 
     * @param string $name
     * @return bool
     */
    protected function isSet(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
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
        if (is_array($namedScopes) && !isset($this->attributes[self::NAMEDSCOPE_NAME])) {
            $this->attributes[self::NAMEDSCOPE_NAME]['name'] = new NamedScope($namedScopes['name'] ?? $namedScopes);
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

        if (is_string($this->service) && !isset($this->service[self::SERVICE_NAME])) {
            $this->attributes[self::SERVICE_NAME] = new Service();
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

        if ((is_array($this->response) || is_string($this->response)) && !isset($this->attributes[self::RESPONSE_NAME])) {
            $this->attributes[self::RESPONSE_NAME] = new Response(type: $this->response['type'] ?? $this->response);
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
     * Returns settings for route attribute.
     * 
     * @return type
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Returns settings for named scope attribute.
     * 
     * @return mixed
     */
    public function getNamedscope()
    {
        return $this->namedscope;
    }

    /**
     * Returns settings for service attribute.
     * 
     * @return bool|null
     */
    public function getService(): ?bool
    {
        return $this->service;
    }

    /**
     * Returns settings for service attribute.
     * 
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns attributes.
     * 
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
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
     * Sets route attribute setting.
     * 
     * @param type $route
     * @return $this
     */
    public function setRoute($route)
    {
        if ($route !== true && $route !== null) {
            throw new ApplicationException('Invalid value for [' . self::ROUTE_NAME . '] setting in pattern [' . $this->patternName . '].',
                            null, null, 926016);
        }
        $this->route = $route;
        return $this;
    }

    /**
     * Sets named scope attribute setting.
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
     * Sets service attribute setting.
     * 
     * @param type $service
     * @return $this
     */
    public function setService($service)
    {
        if (is_bool($service) === false && $service !== null && strtolower($service) !== 'all') {
            throw new ApplicationException('Invalid value for [' . self::SERVICE_NAME . '] setting in pattern [' . $this->patternName . '].',
                            null, null, 926017);
        }
        $this->service = $service;
        return $this;
    }

    /**
     * Sets response attribute setting.
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
     * Sets attribute.
     * 
     * @param type $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    abstract public function processMethod(string $class, string $method);
}
