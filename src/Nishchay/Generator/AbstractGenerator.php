<?php

namespace Nishchay\Generator;

use Nishchay;
use ReflectionClass;
use Nishchay\Exception\InvalidStructureException;
use Nishchay\Exception\ApplicationException;
use Nishchay\Utility\SystemUtility;
use Nishchay\FileManager\SimpleFile;
use Nishchay\Utility\MethodInvokerTrait;
use Nishchay\Console\Printer;
use Nishchay\FileManager\SimpleDirectory;

/**
 * Abstract Generator class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractGenerator
{

    use MethodInvokerTrait;

    /**
     * Name of class or template to be created.
     * 
     * @var string
     */
    protected $name;

    /**
     * Type of generator.
     * 
     * @var string
     */
    protected $type;

    /**
     *
     * @var \ReflectionClass
     */
    protected $reflection;

    /**
     *
     * @var \
     */
    protected $templateMapper;

    /**
     * 
     * @param type $name
     */
    public function __construct($name, $type)
    {
        $this->check();
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * 
     * @throws ApplicationException
     */
    private function check()
    {
        if (Nishchay::isApplicationStageLocal() === false) {
            throw new ApplicationException('Application stage should be local to generate controller.', null, null, 933001);
        }
        if (Nishchay::isApplicationRunningForCommand() === false) {
            throw new ApplicationException('Application not running from command line.', null, null, 933002);
        }
    }

    /**
     * 
     * @throws ApplicationException
     */
    protected function isValidName($template = false, $ask = false)
    {
        return $template ? $this->isValidDirectory() : $this->isValidFile($ask);
    }

    /**
     * Validates file as per structure definition.
     * 
     * @return string
     * @throws ApplicationException
     */
    protected function isValidFile($ask = true)
    {
        $this->isClassExists();

        if ($ask) {
            $directories = Nishchay::getStructureProcessor()->getDirectories($this->type);

            $options = [];
            $i = 1;
            foreach ($directories as $namespace => $path) {
                $options[$i++] = $namespace;
            }
            $options[$i] = 'Select this if you entered class name with namespace';

            $answer = (int) $this->getInput('Where do you want to create(Type number)', $options, 3, true);
            if ($answer !== $i) {
                $this->name = $options[$answer] . '\\' . $this->name;
            }
        }
        $filePath = SystemUtility::refactorDS(ROOT . $this->name . '.php');
        try {
            if (file_exists($filePath)) {
                throw new ApplicationException('File with name [' . $filePath . ']'
                        . ' already exist.', null, null, 933003);
            }
            if (($detail = Nishchay::getStructureProcessor()
                    ->isValidFile($filePath)) === false ||
                    $detail['special'] !== $this->type) {
                goto INVALID;
            }

            $postfix = ucfirst($this->type);
            if ($ask && strpos($filePath, $postfix . '.php') === false) {
                $directory = new SimpleDirectory(dirname($filePath));
                $count = 0;
                $files = $directory->getFiles();
                foreach ($files as $path) {
                    if (strpos($path, $postfix . '.php') !== false) {
                        $count++;
                    }
                }
                if ($count === count($files)) {
                    Printer::write('We have detected that, all ' . $this->type . ' are ends with ' . $postfix . ' in selected directory.' . PHP_EOL, Printer::GREEN_COLOR);
                    Printer::write('So we have applied ' . $postfix . ' at end of file and class name.' . PHP_EOL, Printer::GREEN_COLOR);
                    $this->name = $this->name . $postfix;
                    $filePath = SystemUtility::refactorDS(ROOT . $this->name . '.php');
                } else if ($count !== 0) {
                    Printer::write('We have detected that you do not folllow consistent class name.' . PHP_EOL, Printer::RED_COLOR);
                    Printer::write('Problem: Some class name ends with ' . $postfix . ' and some does not, please maintain consistency.' . PHP_EOL, Printer::RED_COLOR);
                }
            }

            return $filePath;
        } catch (InvalidStructureException $e) {
            INVALID:
            throw new ApplicationException('File [' . $this->name . '] is not valid as'
                    . ' per structure definition.', null, null, 933004);
        }
    }

    /**
     * Validates directory as per structure definition.
     * 
     * @return string
     * @throws ApplicationException
     */
    protected function isValidDirectory()
    {
        $path = SystemUtility::refactorDS(ROOT . $this->name);
        $directories = Nishchay::getStructureProcessor()->getDirectories($this->type);

        $options = [];
        $i = 1;
        foreach ($directories as $namespace => $path) {
            $options[$i++] = $namespace;
        }
        $options[$i] = 'Select this if you have entered namespace';

        $answer = (int) $this->getInput('Where do you want to create(Type number)', $options, 3, true);
        if ($answer !== $i) {
            $this->name = $options[$answer] . '\\' . $this->name;
        }
        $path = SystemUtility::refactorDS(ROOT . $this->name);
        try {
            if (file_exists($path)) {
                $input = $this->getInput('Directory already exist,'
                        . ' Do you create in same'
                        . ' directory?', 'YN');
                if ($input === 'n') {
                    throw new ApplicationException('Operation terminated by user.', null, null, 933005);
                }
                return $path;
            }
            if (($type = Nishchay::getStructureProcessor()
                    ->isValidDirectory($path)) === false ||
                    $type !== $this->type) {
                goto INVALID;
            }
            return $path;
        } catch (InvalidStructureException $e) {
            INVALID:
            throw new ApplicationException('Directory [' . $this->name . '] is not valid as'
                    . ' per structure definition.', null, null, 933006);
        }
    }

    /**
     * Returns content of class.
     * 
     * @return type
     */
    protected function getContent()
    {
        return file_get_contents($this->reflection->getFileName());
    }

    /**
     * Returns class name and namespace.
     * 
     * @return string
     */
    protected function getClassDetail($name = null)
    {
        $separatedClassName = explode('\\', $name === null ? $this->name : $name);
        $shortName = end($separatedClassName);
        array_pop($separatedClassName);
        return [$shortName, implode('\\', $separatedClassName)];
    }

    /**
     * 
     * @param type $class
     * @param type $callback
     * @return type
     */
    protected function createClass($class, $callback = false)
    {
        $filePath = $this->isValidName();
        $this->reflection = new ReflectionClass($class);
        list ($shortClassName, $namespace) = $this->getClassDetail();
        $file = new SimpleFile($filePath, SimpleFile::TRUNCATE_WRITE);
        $content = $this->updateContent($this->getContent(), $namespace, $shortClassName);
        if ($callback) {
            $content = $this->invokeMethod($callback, [$content]);
        }
        $file->write($content);
        return $filePath;
    }

    /**
     * Updates namespace, class name and annotation defined on class.
     * 
     * @param string $content
     * @param string $namespace
     * @param string $class
     * @return string
     */
    protected function updateContent($content, $namespace, $class)
    {
        $this->replaceNamespace($namespace, $content);
        $this->replaceClassName($class, $content);
        $this->removeFrameworkAnnotation($content);
        $search = [
            '{authorName}',
            '{versionNumber}',
            '{' . $this->reflection->getShortName() . 'ClassDescription}'
        ];
        $replace = [
            '@author ' . Nishchay::getApplicationAuthor(),
            '@since ' . Nishchay::getApplicationVersion(),
            $class . ' ' . $this->type . ' class.'];
        return str_replace($search, $replace, $content);
    }

    /**
     * Removes framework annotation defined on class.
     * 
     * @param string $content
     * @return string
     */
    private function removeFrameworkAnnotation(&$content)
    {
        $start = strpos($content, '#ANN_START');
        $content = substr_replace($content, '', $start, (strpos($content, '#ANN_END') - $start) + 12);
    }

    /**
     * Replace class name definition with given class name.
     * 
     * @param string $class
     * @param string $content
     */
    private function replaceClassName($class, &$content)
    {
        $content = str_replace('class ' . $this->reflection
                        ->getShortName(), 'class ' . $class, $content);
    }

    /**
     * 
     * @param type $namespace
     * @param type $content
     */
    private function replaceNamespace($namespace, &$content)
    {
        $content = str_replace('namespace ' . $this->reflection
                        ->getNamespaceName(), 'namespace ' . $namespace, $content);
    }

    /**
     * Creates class from template.
     * 
     * @param string $templateName
     * @return string
     * @throws ApplicationException
     */
    public function createFromTemplate($templateName)
    {
        # If it reutrns false means there's no template defined for requested
        # template name.
        if (($mapping = $this->getMapper()->getMapping($templateName)) === false) {
            throw new ApplicationException('Template [' . $templateName . '] does not exist.', null, null, 933007);
        }

        # This will check if file or class with same already exist or not
        # It will also validate directory to be created with structure definition.
        $path = $this->isValidName($templateName);

        # Asking user whether they want create all controller defined for
        # template. If they say no we will ask for each controller to be created
        # that controller or not.
        $all = $this->getInput('Do you want create all(type Y) '
                        . 'class from template or'
                        . ' specific(type N)?', 'YN') === 'y';

        $created = [];
        $namespace = $this->name;
        foreach ($mapping as $template => $templateClass) {
            if ($all === false) {
                if ($this->getInput('Do you want to'
                                . ' create [' . $template . ']?', 'YN') === 'n') {
                    continue;
                }
            }

            # This will create directory if not exist.
            SystemUtility::resolvePath($path);
            $this->reflection = new ReflectionClass($templateClass);

            # Fetching content from template class then we will replace some
            # content from it.
            $content = $this->getContent();
            list($class) = $this->getClassDetail($templateClass);
            $this->name = $namespace . '\\' . $class;
            $this->isValidName(false, false);

            # Creating write only access to file so that we can
            # write content to it.
            $created[] = $this->name;
            $file = new SimpleFile($path . DS . $class . '.php', SimpleFile::TRUNCATE_WRITE);
            $file->write($this->updateContent($content, $namespace, $class));
        }

        if (empty($created)) {

            Printer::write('No class created.', Printer::RED_COLOR);
            return false;
        }

        return ['path' => $path];
    }

    /**
     * Gets user input from console.
     * 
     * @param string $question
     * @param array $options
     * @param int $try
     * @return type
     * @throws ApplicationException
     */
    protected function getInput($question, $options, $try = 3, $keyIsAnswer = false)
    {
        $tryCount = 1;
        do {
            if (is_array($options)) {
                Printer::write($question . PHP_EOL);
                if (is_array($options)) {
                    $index = 1;
                    foreach ($options as $key => $option) {
                        $options[$key] = strtolower($option);
                        echo ($index++) . '. ' . $option . PHP_EOL;
                    }
                }
                $inputLine = 'Provide your input';
            } else {
                $inputLine = $question;
            }

            $input = readline($inputLine . ' : ');
            if (!empty($input)) {
                if (is_array($options)) {

                    if ($keyIsAnswer) {
                        if (isset($options[$input])) {
                            return $input;
                        }
                    } else {
                        $optionsLowered = array_map('strtolower', $options);
                        if (in_array(strtolower($input), $optionsLowered)) {
                            return $input;
                        }
                    }
                } else if ($options === 'YN') {
                    if (in_array(strtolower($input), ['y', 'n'])) {
                        return strtolower($input);
                    }
                } else {
                    return $input;
                }
            }
            $tryCount++;
        } while ($tryCount <= ($try === false ? 3 : $try));

        if ($try !== false) {
            throw new ApplicationException('Operation terminated after'
                    . ' limit exceeded to get input.', null, null, 933008);
        }
    }

    /**
     * 
     */
    abstract public function getMapper();

    /**
     * 
     */
    abstract protected function isClassExists();
}
