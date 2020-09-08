<?php

namespace Nishchay\Processor;

use Exception;
use ReflectionClass;
use ReflectionProperty;
use Nishchay\Processor\Facade;
use Nishchay\Exception\InvalidStructureException;
use Nishchay\Exception\ApplicationException;
use Nishchay\Processor\Processor;
use Nishchay\Data\Connection\Connection;
use Nishchay\Processor\Loader\Loader;
use Nishchay\Route\Collection as RouteCollection;
use Nishchay\Controller\Collection as ControllerCollection;
use Nishchay\Data\Collection as EntityCollection;
use Nishchay\Handler\Collection as HandlerCollection;
use Nishchay\Event\Collection as EventCollection;
use Nishchay\Mail\Collection as MailCollection;
use Nishchay\Cache\Collection as CacheCollection;
use Nishchay\Security\Encrypt\Collection as EncryptCollection;
use Nishchay\Container\Collection as ContainerCollection;
use Nishchay\Handler\Dispatcher;
use Nishchay\Processor\Structure\StructureProcessor;
use Nishchay\Persistent\System as SystemPersistent;
use Nishchay\Processor\EnvironmentVariables;
use Nishchay\Logger\Logger;
use Nishchay\Lang\Lang;

/**
 * Core of the application
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
final class Application
{

    /**
     * Nishchay framework version number.
     */
    const VERSION = '1.0';

    /**
     * Nishchay framework version name.
     */
    const VERSION_NAME = 'Namaste';

    /**
     * Nishchay framework name.
     */
    const NAME = 'Nishchay Framework';

    /**
     * Development/Local stage of the application.
     */
    const STAGE_LOCAL = 'local';

    /**
     * UAT/Test stage of the application.
     */
    const STAGE_TEST = 'test';

    /**
     * Live stage of the application.
     */
    const STAGE_LIVE = 'live';

    /**
     * Application running other than console.
     */
    const RUNNING_NO_CONSOLE = 0;

    /*
     * Application running for the tests.
     */
    const RUNNING_CONSOLE_TEST = 1;

    /**
     * Application running for the console command.
     */
    const RUNNING_CONSOLE_COMMAND = 2;

    /**
     * Application running from.
     * 
     * @var int
     */
    private $runningFrom = null;

    /**
     * version of application.
     * 
     * @var number 
     */
    private $applicationVersion = '1.0';

    /**
     * information about user application.
     * 
     * @var object 
     */
    private $applicationName = FALSE;

    /**
     * Author Name of application.
     * 
     * @var string 
     */
    private $applicationAuthor = "";

    /**
     * Application stage
     * This can be 
     *  1. local
     *  2. test
     *  3. live
     * 
     * @var type 
     */
    private $applicationStage = false;

    /**
     * Segments of processing request.
     * 
     * @var array 
     */
    public $segment;

    /**
     * Supported file extensions.
     * 
     * @var array 
     */
    private $supportedExtension = [];

    /**
     * Default landing route.
     * 
     * @var string 
     */
    private $landingRoute = NULL;

    /**
     * Configuration Loader instance.
     * 
     * @var \Nishchay\Processor\Loader\Loader 
     */
    private $configurationLoader;

    /**
     *
     * @var \Nishchay\Processor\Structure\StructureProcessor 
     */
    private $structureProcessor;

    /**
     * All registered instances.
     * 
     * @var array 
     */
    private $registered = [];

    /**
     * Initialization.
     * 
     * @param \Nishchay\Processor\Loader\Loader      $loader
     */
    public function __construct(Loader $loader)
    {
        $this->configurationLoader = $loader;
        Facade::create($this, 'Nishchay');
        $this->init();
    }

    /**
     * Returns application author name.
     * 
     * @return string
     */
    public function getApplicationAuthor()
    {
        return $this->applicationAuthor;
    }

    /**
     * Returns application name.
     * 
     * @return string
     */
    public function getApplicationName()
    {
        return $this->applicationName;
    }

    /**
     * Returns application stage which has been set.
     * 
     * @return string
     */
    public function getApplicationStage()
    {
        return $this->applicationStage;
    }

    /**
     * Returns current application version number.
     * 
     * @return string
     */
    public function getApplicationVersion()
    {
        return $this->applicationVersion;
    }

    /**
     * 
     * @param string $name
     * @return mixed
     */
    public function getConfig($name)
    {
        return $this->configurationLoader->getValue('application.' . $name);
    }

    /**
     * Returns true if application stage is production/live.
     * 
     * @return boolean
     */
    public function isApplicationStageLive()
    {
        return $this->applicationStage === self::STAGE_LIVE;
    }

    /**
     * Returns true if application stage is in testing phase.
     * 
     * @return boolean
     */
    public function isApplicationStageTest()
    {
        return $this->applicationStage === self::STAGE_TEST;
    }

    /**
     * Returns true if application stage is local/development.
     * 
     * @return boolean
     */
    public function isApplicationStageLocal()
    {
        return $this->applicationStage === self::STAGE_LOCAL;
    }

    /**
     * Returns default landing route of application.
     * 
     * @return string
     */
    public function getLandingRoute()
    {
        return $this->landingRoute . '';
    }

    /**
     * Supported file extensions of application.
     * 
     * @return array
     */
    public function getSupportedExtension()
    {
        return $this->supportedExtension;
    }

    /**
     * 
     * @return  NULL
     * @throws  InvalidStructureException
     */
    private function init()
    {
        if (file_exists(SETTINGS . 'constants.php')) {
            require_once SETTINGS . 'constants.php';
        }

        $this->configurationLoader->deepRequired(SETTINGS . 'functions');
        $this->setApplicationInformation();
    }

    /**
     * Register instance with given name.
     * 
     * @param type $name
     * @param type $instance
     */
    private function register($name, $instance)
    {
        $this->registered[$name] = $instance;
    }

    /**
     * Returns Logger instance.
     * 
     * @return \Nishchay\Logger\Logger
     */
    public function getLogger()
    {
        if ($this->getSetting('logger.enable') === false) {
            throw new ApplicationException('Logger is disabled. To enable set'
                    . ' enable = true in logger.php file.', null, null, 925026);
        }
        if (array_key_exists('logger', $this->registered) === false) {
            $this->register('logger', new Logger());
        }
        return $this->registered['logger'];
    }

    /**
     * Returns route collection instance.
     * 
     * @return \Nishchay\Route\Collection
     */
    public function getRouteCollection()
    {
        return $this->getInstance(RouteCollection::class);
    }

    /**
     * Returns controller collection instance.
     * 
     * @return \Nishchay\Controller\Collection
     */
    public function getControllerCollection()
    {
        return $this->getInstance(ControllerCollection::class);
    }

    /**
     * Returns exception handler.
     * 
     * @return \Nishchay\Handler\Dispatcher
     */
    public function getExceptionHandler()
    {
        return $this->getInstance(Dispatcher::class);
    }

    /**
     * Returns entity collection instance.
     * 
     * @return \Nishchay\Data\Collection
     */
    public function getEntityCollection()
    {
        return $this->getInstance(EntityCollection::class);
    }

    /**
     * Returns handler collection instance.
     * 
     * @return \Nishchay\Handler\Collection
     */
    public function getHandlerCollection()
    {
        return $this->getInstance(HandlerCollection::class);
    }

    /**
     * Returns event collection instance.
     * 
     * @return \Nishchay\Handler\Collection
     */
    public function getEventCollection()
    {
        return $this->getInstance(EventCollection::class);
    }

    /**
     * Returns value from environment variable.
     * 
     * @param string $name
     * @return mixed
     */
    public function getEnv($name)
    {
        return $this->getInstance(EnvironmentVariables::class)->get($name);
    }

    /**
     * Returns instance of container collection.
     * 
     * @return \Nishchay\Container\Collection
     */
    public function getContainerCollection()
    {
        return $this->getInstance(ContainerCollection::class);
    }

    /**
     * Returns container.
     * 
     * @param string $class
     * @return type
     */
    public function getContainer(string $class)
    {
        return $this->getContainerCollection()->get($class);
    }

    /**
     * Returns instance of given class.
     * 
     * @param string $class
     * @return type
     */
    private function getInstance(string $class)
    {
        if (array_key_exists($class, $this->registered) == false) {

            if (method_exists($class, __FUNCTION__)) {
                $instance = call_user_func([$class, __FUNCTION__]);
            } else {
                $instance = new $class;
            }

            $this->register($class, $instance);
        }

        return $this->registered[$class];
    }

    /**
     * Returns Lang instance.
     * 
     * @return \Nishchay\Lang\Lang
     */
    public function getLang()
    {
        return Lang::getInstance();
    }

    /**
     * Returns Lang instance.
     * 
     * @return \Nishchay\Security\Encrypt\Encrypter
     */
    public function getEncrypter($name = null)
    {
        return EncryptCollection::getInstance()->get($name);
    }

    /**
     * Returns Mail instance.
     * 
     * @param string $name
     * @return \Nishchay\Mail\Mail
     */
    public function getMail($name = null)
    {
        return MailCollection::getInstance()->get($name);
    }

    /**
     * 
     * @param string $name
     * @return \Nishchay\Cache\CacheHandler
     */
    public function getCache($name = null)
    {
        return CacheCollection::getInstance()->get($name);
    }

    /**
     * Returns structure processor.
     * 
     * @return \Nishchay\Processor\Structure\StructureProcessor
     */
    public function getStructureProcessor()
    {
        if ($this->structureProcessor !== null) {
            return $this->structureProcessor;
        }

        if (SystemPersistent::isPersisted('struct')) {
            return $this->structureProcessor = SystemPersistent::getPersistent('struct');
        }

        $this->structureProcessor = new StructureProcessor(CONFIG . 'structure.xml');
        if ($this->isApplicationStageLive()) {
            SystemPersistent::setPersistent('struct', $this->structureProcessor);
        }

        return $this->structureProcessor;
    }

    /**
     * Application starts from here
     * 
     */
    public function run($runningFrom)
    {
        if (!is_int($runningFrom) || $runningFrom > 2) {
            throw new Exception('Application running from invalid place.', null, null, 925027);
        }
        $this->runningFrom = $runningFrom;

        $processor = Facade::create(new Processor(), 'Processor', [], true);
        $processor->start();
    }

    /**
     * Returns true if application running for other than console.
     * 
     * @return boolean
     */
    public function isApplicationRunningNoConsole()
    {
        return $this->runningFrom === static::RUNNING_NO_CONSOLE;
    }

    /**
     * Returns true if application running for tests.
     * 
     * @return boolean
     */
    public function isApplicationRunningForTests()
    {
        return $this->runningFrom === static::RUNNING_CONSOLE_TEST;
    }

    /**
     * Returns true if application running for console command.
     * 
     * @return boolean
     */
    public function isApplicationRunningForCommand()
    {
        return $this->runningFrom === static::RUNNING_CONSOLE_COMMAND;
    }

    /**
     * Application information
     * 
     * @return NULL
     */
    private function setApplicationInformation()
    {
        if ($this->getConfig('application') !== false) {
            foreach ($this->getConfig('application') as $key => $value) {
                $app = 'application' . ucfirst($key);
                if (isset($this->$app)) {
                    $this->$app = $value;
                }
            }
        }
        $this->landingRoute = $this->getConfig('config.landingRoute');
        if ($this->landingRoute === false || empty($this->landingRoute)) {
            throw new InvalidStructureException('Please spcify landing route'
                    . ' in application configuration setting.', null, null, 925028);
        }

        if ($this->isApplicationStageLive()) {
            error_reporting(0);
        }

        return $this->setSupportedExtension();
    }

    /**
     * Sets data connection configuration to database conneciton class.
     */
    private function setDataConnection()
    {
        # Databse configuration.
        # Databse configuration are stored in Data Connection class.
        # Below we are using reflection class to set private property 
        # of Connection class.
        $datbaseConfig = $this->configurationLoader->getConfig('database');
        $reflection = new ReflectionClass(new Connection());

        # Registering all defined connections to connection class.
        $this->setClassProperty($reflection->getProperty(Connection::CONNECTIONS), $datbaseConfig->connections);

        # Registering default connection to connection class.
        $this->setClassProperty($reflection->getProperty(Connection::DEFAULT_CONNECTION), $datbaseConfig->default);
    }

    /**
     * Sets value of class property.
     * 
     * @param   ReflectionProperty        $reflection
     * @param   mixed                     $value
     */
    private function setClassProperty(ReflectionProperty $reflection, $value)
    {
        $reflection->setAccessible(true);
        $reflection->setValue($value);
    }

    /**
     * Returns and sets supported file extensions as defined config.xml in array.
     * 
     * @return array
     * @throws ApplicationException
     */
    private function setSupportedExtension()
    {
        $this->supportedExtension = $this->getConfig('config.fileTypes');

        if ($this->supportedExtension === false || empty($this->supportedExtension)) {
            throw new ApplicationException('Missing File types. Should be'
                    . ' defined in [config.fileTypes] separated by pipeline.', null, null, 925029);
        }

        $this->supportedExtension = explode('|', $this->supportedExtension);
        return $this->setDataConnection();
    }

    /**
     * 
     * @param type $name
     * @return \Nishchay\Processor\Loader\Loader
     */
    public function getSetting($name)
    {
        return $this->configurationLoader->getValue($name);
    }

}
