<?php

namespace Nishchay\Processor;

use BadMethodCallException;
use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\AlreadyInstanciatedExecption;
use ReflectionClass;
use Nishchay\Utility\Coding;
use Nishchay\Annotation\AnnotationParser;

/**
 * Base Facade class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Facade
{

    /**
     * All objects of facade created using this class only.
     * 
     * @var type 
     */
    private static $instances = [];

    /**
     * Instance of this class
     * 
     * @var object 
     */
    private static $instance = null;

    /**
     * List of primary facades which are required for Nishchay processor.
     * 
     * @var array 
     */
    private $primary = [
        AnnotationParser::class => 'AnnotationParser'
    ];

    /**
     * Creates primary facades.
     * Because this class throws exception if instance of this class
     * already been created.
     * 
     * @throws \Nishchay\Exception\AlreadyInstanciatedExecption
     */
    public function __construct()
    {
        $this->createPrimaryFacade();

        if (self::$instance !== NULL) {
            throw new AlreadyInstanciatedExecption('Class [' . __CLASS__ .
                    '] already been instanciated.', null, null, 925030);
        }


        self::$instance = $this;
    }

    /**
     * To call actual method on calling facade class.
     * 
     * @param   string  $name
     * @param   array   $arguments
     * @return  mixed
     * @throws  Exception
     * @throws  BadMethodCallException
     */
    public static function __callStatic($name, $arguments)
    {
        $callingClass = get_called_class();

        if (!array_key_exists($callingClass, self::$instances)) {
            throw new ApplicationException('Facade not created.', null, null, 925032);
        }

        $object = self::$instances[$callingClass];

        if ($name === 'me') {
            return $object;
        }

        if (!method_exists($object, $name)) {
            throw new BadMethodCallException('Method [' . $callingClass .
                    '::' . $name . '] does not exist in Facade class.', null, null, 925031);
        }

        return call_user_func_array([$object, $name], $arguments);
    }

    /**
     * Creates new facade for given class.
     * 
     * @param   string  $class  Should instance of class or its name.
     * @param   string  $name   Facade name to be created.
     * @param   array|string    $params __construct parameters.
     * @param   boolean $return Flag for returning class instance.
     * @return  object|NULL            
     * @throws  \Nishchay\Exception\AlreadyInstanciatedExecption
     * @throws \Nishchay\Exception\NotSupportedException
     * @throws \InvalidArgumentException
     */
    public static function create($class, $name, $params = [], $return = false)
    {
        # Must be called using Facade class only.
        if (get_called_class() !== Facade::class) {
            throw new ApplicationException('Method [create] does not'
                    . ' belogs to class [' . get_called_class() . '].', null, null, 925033);
        }

        if (isset(self::$instances[$name])) {
            throw new ApplicationException('Facade alias [' . $name
                    . '] already exist.', null, null, 925034);
        }

        # Will have to create instance if class name is string.
        Coding::createClassAlias(__CLASS__, $name);
        self::$instances[$name] = self::createInstnace($class, $params);

        return $return ? self::$instances[$name] : true;
    }

    private static function createInstnace($class, $params)
    {
        if (is_object($class)) {
            return $class;
        }

        if (is_string($class)) {
            $reflection = new ReflectionClass($class);
            return $reflection->newInstanceArgs(is_array($params) ?
                            $params : [$params]);
        }


        throw new ApplicationException('First parameter must be'
                . ' either string or object.', null, null, 925035);
    }

    /**
     * Creates primary facade which are used for processing Nishchay.
     */
    private function createPrimaryFacade()
    {
        if (self::$instance !== null) {
            return;
        }

        foreach ($this->primary as $class => $name) {
            self::create($class, $name);
        }
    }

}
