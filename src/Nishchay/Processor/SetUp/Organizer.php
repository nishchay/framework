<?php

namespace Nishchay\Processor\SetUp;

use Nishchay;
use Exception;
use Nishchay\Exception\InvalidStructureException;
use Nishchay\Exception\NotSupportedException;
use ReflectionClass;
use AnnotationParser;
use Nishchay\Processor\Structure\Structure;
use Nishchay\Controller\Annotation\Controller as ControllerAnnotation;
use Nishchay\Event\Annotation\Event as EventAnnotation;
use Nishchay\Http\View\Collection as ViewCollection;
use Nishchay\FileManager\SimpleDirectory;
use Nishchay\Utility\StringUtility;
use Nishchay\Processor\AbstractCollection;
use Nishchay\Processor\Annotation\ClassType;

/**
 * Application class and view organizing according to their types.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Organizer
{

    /**
     * Supported class types
     * 
     * @var array 
     */
    private $specialClasses = [
        'controller', 'entity', 'event', 'handler'
    ];

    /**
     * Structure Processor object
     * 
     * @var \Nishchay\Processor\Structure\StructureProcessor 
     */
    private $structureProcessor = null;

    /**
     * Supported file extensions as defined in main config file
     * 
     * @var type 
     */
    private $supportedFiles = [];

    /**
     * Current validation mode for the processing file.
     * 
     * @var string 
     */
    private $currentValidationMode = '';

    /**
     * Curent processing class type
     * 
     * @var type 
     */
    private $currentType = '';

    /**
     * Current Parent Directory.
     * 
     * @var string 
     */
    private $currentContext = '';

    /**
     * Initial Processing
     */
    public function __construct()
    {
        $this->init();

        # Process each file and directory in Application Directory.
        $this->processApplication(APP);

        AbstractCollection::close();
    }

    /**
     * Initialization
     */
    private function init()
    {

        # Application Supported files as defined in settting > application > configuration.
        $this->setSupportedFiles();

        $this->structureProcessor = Nishchay::getStructureProcessor();
    }

    /**
     * Type of file can be either class or view
     * 
     * @param string $file
     * @return string
     */
    public function currentFileIs($file)
    {
        foreach (token_get_all(file_get_contents($file)) as $token) {
            if ($token[0] === T_NAMESPACE) {
                return Structure::FILE_TYPE_CLASS;
            } else if ($token[0] === T_INLINE_HTML) {
                return Structure::FILE_TYPE_VIEW;
            }
        }

        return Structure::FILE_TYPE_VIEW;
    }

    /**
     * Find class name in given path and returns it.
     * 
     * @param   string                      $path
     * @return  string
     * @throws  InvalidStructureException
     */
    public function getClassName($path)
    {
        # Extracting class nane from the file. path
        $match = [];
        preg_match('#' . preg_quote(ROOT) . '(.*?)\.(.*)#', $path, $match);
        $class = str_replace('/', '\\', $match[1]);

        if (!class_exists($class)) {
            throw new InvalidStructureException('Class [' . $class . '] not'
                    . ' found in file [' . $path . '].', null, null, 925005);
        }
        return $class;
    }

    /**
     * Returns current class type 
     * 
     * @param   array       $annotations
     * @return  array
     */
    public function getClassType($annotations)
    {
        return array_intersect($this->specialClasses, array_keys($annotations));
    }

    /**
     * Returns parent of file
     * 
     * @param   string  $path
     * @param   string  $node
     * @return  string
     */
    public function setContext($path, $node)
    {
        $relativePath = str_replace([ROOT, '\\', '/'], ['', '\\', '\\'], $path);
        preg_match("#(.*)(?=\\\\{$node}\\\\)#", $relativePath, $match);
        $this->currentContext = $match[0];
    }

    /**
     * Returns supported file extensions.
     * 
     * @return array
     */
    public function getSupportedFile()
    {
        return $this->supportedFiles;
    }

    /**
     * Checks if file extension is supported or not.
     * 
     * @param   string      $file
     * @return  boolean
     */
    protected function isFileSupported($file)
    {
        return in_array(StringUtility::getExplodeLast('.', $file), $this->getSupportedFile());
    }

    /**
     * Checks given current class type is same as current validation mode
     * 
     * @return boolean
     */
    private function isValidClass()
    {
        if ($this->isOtherMode()) {
            $this->currentType = Structure::FILE_TYPE_OTHER;
            return TRUE;
        } else {
            return $this->currentValidationMode === $this->currentType;
        }
    }

    /**
     * Returns true current validation mode is View
     * 
     * @return boolean
     */
    private function isViewMode()
    {
        return $this->currentValidationMode === Structure::FILE_TYPE_VIEW;
    }

    /**
     * Returns true if current validation mode is any type.
     * 
     * @return boolean
     */
    private function isOtherMode()
    {
        return $this->currentValidationMode === Structure::FILE_TYPE_OTHER;
    }

    /**
     * Returns true if current file type is class.
     * 
     * @return boolean
     */
    private function isClass()
    {
        return $this->currentType === Structure::FILE_TYPE_CLASS;
    }

    /**
     * Processing Application codes
     * 
     * @param string $applicationPath 
     */
    public function processApplication($applicationPath)
    {
        $this->processDirectory($applicationPath);
        $directory = new SimpleDirectory($applicationPath);
        $directory->walk(function($path) {
            if (strpos(basename($path), '.') === 0) {
                return;
            }
            if (is_dir($path)) {
                $this->processDirectory($path);
            } else if (is_file($path)) {
                $this->processFile($path);
            } else {
                throw new Exception('Nishchay not able to detect file type'
                        . ' for file [' . $path . '].', null, null, 925006);
            }
        }, [], true);

        Nishchay::getRouteCollection()->sort();
        Nishchay::getEventCollection()->persist();
        Nishchay::getHandlerCollection()->persist();
    }

    /**
     * Process controller class.
     * 
     * @param   string      $class
     * @param   array       $annotation
     * @param   string      $context
     */
    protected function processControllerClass($class, $annotation, $context)
    {

        new ControllerAnnotation($class, $annotation, $context);
    }

    /**
     * Process event class.
     * 
     * @param   string      $class
     * @param   array       $annotation
     */
    protected function processEventClass($class, $annotation)
    {
        Nishchay::getEventCollection()->register($class);
        new EventAnnotation($class, $annotation);
    }

    /**
     * Register Entity class.
     * 
     * @param string $class
     */
    protected function processEntityClass($class)
    {
        Nishchay::getEntityCollection()->register($class);
    }

    /**
     * 
     * @param string $class
     */
    protected function processHandlerClass($class, $annotation, $context)
    {
        Nishchay::getHandlerCollection()->store($class, $annotation, $context);
    }

    /**
     * Validates directory path with defined structure and 
     * returns information about what kind of inner content should be
     * 
     * @param   string  $path
     */
    private function processDirectory($path)
    {
        $this->structureProcessor->isValidDirectory($path);
    }

    /**
     * 
     * @param   string  $path
     */
    private function processFile($path)
    {

        $detail = $this->structureProcessor->isValidFile($path);
        $this->currentValidationMode = $detail['special'];
        if ($this->isFileSupported($path) === false) {
            throw new InvalidStructureException('File [' . $path . '] not supported.', null, null, 925007);
        }

        $this->setContext($path, $detail['node']);

        # File can be view or class.
        $this->currentType = $this->currentFileIs($path);
        if ($this->isViewMode()) {
            return $this->storeView($path);
        }

        # When current mode is other and current type is class
        # we should check for class name standard.
        if ($this->isOtherMode()) {
            return $this->isClass() && $this->getClassName($path);
        }

        # Last mode always special type so current type should be class.
        if (!$this->isClass()) {
            throw new InvalidStructureException('[' . $path . '] is'
                    . ' not in namespace. Each class must have namespace.', null, null, 925008);
        }

        $this->properRefactor($this->getClassName($path));
        return TRUE;
    }

    /**
     * 
     * @param   string                      $path
     * @throws  InvalidStructureException
     */
    private function storeView($path)
    {
        if ($this->isClass()) {
            throw new InvalidStructureException('Class file [' . $path .
                    '] not allowed here as per Strucuture definition.', null, null, 925009);
        }
        return ViewCollection::store($this->currentContext);
    }

    /**
     * Properly factoring to their class type
     * From the Defined annotations class type is detected
     * 
     * @param   string                      $class
     * @return  boolean
     * @throws  NotSupportedException
     */
    private function properRefactor($class)
    {
        $reflection = new ReflectionClass($class);

        $classAnnotation = AnnotationParser::getAnnotations($reflection->getDocComment());

        if (!is_array($classAnnotation)) {
            throw new InvalidStructureException('No annotation found on'
                    . ' class [' . $class . '].', null, null, 925010);
        }
        $classType = $this->getClassType($classAnnotation);

        # Class type should be one kind of.
        if (count($classType) > 1) {
            throw new NotSupportedException('Class can only be any one '
                    . 'but can not be [' . implode(',', $classType) . '] together.', $class, null, 925011);
        }

        if (current($classType)) {
            $this->currentType = current($classType);
        } else if (!empty($classAnnotation) && !in_array($this->currentValidationMode, $this->specialClasses)) {
            $classType = new ClassType($class, $classAnnotation);
            $this->currentType = strtolower($classType->getClasstype());
        } else {
            $this->currentType = 'class';
        }

        # Class type should be same as current class type mode
        if (!$this->isValidClass()) {
            throw new InvalidStructureException($reflection->getFileName() .
                    ' should be ' . $this->currentValidationMode);
        }

        $method = 'process' . ucfirst($this->currentType) . 'Class';
        if (method_exists($this, $method)) {
            $this->{$method}($class, $classAnnotation, $this->currentContext);
        }
    }

    /**
     * Gets Supported File Extension as defined main config file
     * To set in this class variable $supported_files
     * 
     */
    private function setSupportedFiles()
    {
        $this->supportedFiles = Nishchay::getSupportedExtension();
    }

}
