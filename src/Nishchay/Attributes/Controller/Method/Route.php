<?php

namespace Nishchay\Attributes\Controller\Method;

use Nishchay\Attributes\AttributeTrait;
use Nishchay\Controller\ControllerClass;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Processor\Application;

/**
 * Trait for invoking method.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
#[\Attribute]
class Route
{

    use AttributeTrait;

    const NAME = 'route';

    /**
     * All valid request methods.
     * 
     * @var array 
     */
    const REQUEST_METHODS = ['GET', 'POST', 'PUT', 'DELETE',
        'OPTIONS', 'HEAD', 'TRACE', 'CONNECT'];

    /**
     * 
     * @var array
     */
    private array $placeholder = [];

    /**
     * Is route prepared from pattern.
     * 
     * @var bool
     */
    private $isPatterned = false;

    /**
     * 
     * @param bool|string $path
     * @param string|array $type
     * @param bool $prefix
     * @param bool $incoming
     * @param string|array $stage
     */
    public function __construct(private bool|string $path = false,
            private string|array $type = [], private bool $prefix = true,
            private bool $incoming = true, private string|array $stage = [])
    {
        $this->refactorType()
                ->validateStage();
    }

    /**
     * Returns true if route prepared from pattern.
     * 
     * @return bool
     */
    public function isPatterned(): bool
    {
        return $this->isPatterned;
    }

    /**
     * 
     * @param type $type
     * @return $this
     */
    private function refactorType()
    {
        $this->type = array_map(function ($value) {
            return strtoupper($value);
        }, (array) $this->type);

        return $this;
    }

    /**
     * Sets route stage.
     * 
     * @param array $stage
     * @return $this
     * @throws InvalidAnnotationExecption
     */
    protected function validateStage()
    {
        $stage = (array) $this->stage;
        $allowed = [Application::STAGE_LOCAL, Application::STAGE_TEST];
        foreach ($stage as $index => $name) {
            $name = strtolower($name);
            if (!in_array($name, $allowed)) {
                throw new InvalidAnnotationExecption('Invalid stage for the route, it should be local or test.',
                                $this->class, $this->method, 926);
            }
            $stage[$index] = $name;
        }

        $this->stage = $stage;
        return $this;
    }

    /**
     * 
     * @param ControllerClass $controller
     * @throws InvalidAnnotationExecption
     */
    public function refactorPath(ControllerClass $controller)
    {
        if ($this->path === true) {
            $this->path = $this->method;
        }

        # We wiil prefix annotation if controler class has @routing annotaiton
        # and prefix parameter value is TRUE.
        # When prefix is FALSE, we ignore prefixing of route.
        if ($controller->getRouting() !== null && $this->prefix === true) {
            $this->path = $controller->getRouting()->getPrefix() . '/' . $this->path;
        }

        $this->path = trim($this->path, '/');

        if (empty($this->path)) {
            throw new InvalidAnnotationExecption('Route path should not be empty.',
                            $this->class, $this->method, 926006);
        }

        # We here now preg quoting path except curly bracket start & end and 
        # double question mark.
        # We will replace this with their regualr expression while storing into
        # collection.
        $this->path = str_replace(['\?', '\{', '\}'], ['?', '{', '}'],
                preg_quote($this->path));

        # Let's find if there any sepecial segment in route path.
        preg_match_all('#(\{)+(\w+)+(\})#', $this->path, $match);
        $this->placeholder = $match[2];
        
        return $this;
    }

    /**
     * Returns all valid request methods.
     * 
     */
    public function getValidRequestMethods()
    {
        return self::REQUEST_METHODS;
    }

}
