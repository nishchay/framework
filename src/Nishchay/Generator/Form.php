<?php

namespace Nishchay\Generator;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Data\Reflection\DataClass;
use Nishchay\Data\Reflection\DataProperty;
use Nishchay\Data\Annotation\Property\Property;
use Nishchay\Console\Printer;
use Nishchay\FileManager\SimpleFile;
use Nishchay\Form\Form as RequestForm;
use Nishchay\Http\Request\Request;
use Nishchay\Processor\VariableType;
use Nishchay\Utility\SystemUtility;

/**
 * Entity Generator class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Form extends AbstractGenerator
{

    /**
     * Tracker properties.
     * 
     * @var array 
     */
    private $trackerProperties = [];

    /**
     * Property types.
     * 
     * @var array
     */
    private $propertyTypes = [];

    public function __construct($name)
    {
        parent::__construct($name, 'form');
        $this->trackerProperties = Nishchay::getSetting('prototype.tracker');
        if (is_array($this->trackerProperties) === false) {
            $this->trackerProperties = [];
        }

        $this->propertyTypes = Nishchay::getSetting('prototype.types');

        if (is_object($this->propertyTypes) === false) {
            $this->propertyTypes = new \stdClass();
        }
    }

    /**
     * Returns data class instance of $class.
     * 
     * @param string $name
     * @return \Nishchay\Data\Reflection\DataClass
     */
    private function getDataClass($name)
    {
        $class = Nishchay::getEntityCollection()->locate($name);

        if ($class === null) {
            throw new ApplicationException('Entity [' . $name . '] not found.', null, null, 933016);
        }

        if ($class !== $name) {
            Printer::write('Located: ');
            Printer::yellow($class . PHP_EOL);
        }

        return new DataClass($class);
    }

    /**
     * Creates form class from entity
     */
    public function createFromEntity($csrf = true)
    {
        $dataClass = $this->getDataClass($this->name);
        $namespace = $this->getNamespace();

        $name = $this->name;
        $this->name = $namespace . '\\' . $this->name;

        $this->isValidFile(false);

        $filePath = SystemUtility::refactorDS(ROOT . $namespace . DS . $name . '.php');
        $file = new SimpleFile($filePath, SimpleFile::TRUNCATE_WRITE);

        # Writing start of class.
        $file->write($this->getClassStartCode($namespace, $name, $csrf) . PHP_EOL . PHP_EOL);

        foreach ($dataClass->getProperties() as $property) {
            if ($property->isIdentity() || $property->isDerived() || $property->isPrimitiveType() === false) {
                continue;
            }

            if (in_array($property->getName(), $this->trackerProperties)) {
                continue;
            }
            $file->write($this->getMethodCode($property) . PHP_EOL . PHP_EOL);
        }

        # Ending class
        $file->write($this->getClassEndCode());

        # Close
        $file->close();

        # Inform
        Printer::write('Created at ');
        Printer::yellow($filePath . PHP_EOL);

        return $this->name;
    }

    /**
     * Returns method code which return form field for the property.
     * 
     * @param DataProperty $property
     * @return string
     */
    private function getMethodCode(DataProperty $property): string
    {
        $propertyName = $property->getName();
        $method = 'get' . ucfirst($propertyName);

        $methodName = 'newInput';
        $more = '';

        # Form field type
        $type = $this->propertyTypes->{$propertyName} ?? 'text';

        # If there values parameter set in @DataType annotation, we will create
        # input choice radio field.
        if (!empty($property->getProperty()->getDatatype()->getValues())) {
            $methodName = 'newInputChoice';

            $values = $property->getProperty()->getDatatype()->getValues();

            # Preparing choices for the field.
            foreach ($values as $key => $value) {
                unset($values[$key]);
                $value = addslashes($value);
                $values[] = "'{$value}' => '" . $value . "'";
            }
            $values = '[' . implode(', ', $values) . ']';

            # Creating call method code.
            $more = PHP_EOL . <<<CHOICES
                        ->setChoices({$values})
CHOICES;
            $type = 'radio';
        }

        # Creating form field method
        return <<<METHOD
    /**
     * Returns form field for {$propertyName}.   
    */
    public function {$method}()
    {
        return \$this->{$methodName}('{$propertyName}', '{$type}'){$more}{$this->getPropertyValidationCode($property->getProperty())};
    }
METHOD;
    }

    /**
     * Returns code for form field validation.
     * 
     * @param Property $property
     * @return string
     */
    private function getPropertyValidationCode(Property $property): string
    {
        $validation = [];
        $dataType = $property->getDatatype();

        # Is property required.
        if ($dataType->getRequired() === true) {
            $validation[] = <<<VALIDATION
                        ->isRequired()
VALIDATION;
        }

        if ($dataType->getLength() !== null) {
            $validation[] = <<<VALIDATION
                        ->setValidation('string:max', {$this->getParameterCode([$dataType->getLength()])})
VALIDATION;
        }

        if (is_array($dataType->getValues())) {
            $validation[] = <<<VALIDATION
                        ->setValidation('enum', {$this->getParameterCode($dataType->getValues())})
VALIDATION;
        }

        if ($dataType->getType() === VariableType::DATE) {
            $validation[] = <<<VALIDATION
                        ->setValidation('date:format', 'Y-m-d')
VALIDATION;
        } else if ($dataType->getType() === VariableType::DATETIME) {
            $validation[] = <<<VALIDATION
                        ->setValidation('date:format', 'Y-m-d H:i:s')
VALIDATION;
        }

        foreach ($property->getValidation() as $rule) {

            # Creating validation parameter code.
            $parameters = $rule->getParameter();
            if (!empty($parameters)) {
                if (count($parameters) === 1) {
                    list($parameters) = $parameters;
                    if (is_string($parameters)) {
                        $parameters = '\'' . addslashes($parameters) . '\'';
                    }
                } else {
                    $parameters = $this->getParameterCode($parameters);
                }
                $parameters = ', ' . $parameters;
            } else {
                $parameters = '';
            }
            $validation[] = <<<VALIDATION
                        ->setValidation('{$rule->getActualRule()}'{$parameters})
VALIDATION;
        }

        if (empty($validation)) {
            return '';
        }

        return PHP_EOL . implode(PHP_EOL, $validation);
    }

    /**
     * Returns parameter for the validation.
     * 
     * @param string $parameters
     * @return type
     */
    private function getParameterCode(array $parameters): string
    {
        foreach ($parameters as $key => $value) {
            if (is_string($value)) {
                $parameters[$key] = '\'' . addslashes($value) . '\'';
            }
        }

        return '[' . implode(',', $parameters) . ']';
    }

    /**
     * Returns class start code.
     * 
     * @param string $namespace
     * @param string $name
     * @return string
     */
    private function getClassStartCode(string $namespace, string $name, bool $csrf): string
    {
        $formName = strtolower($name) . '-form';
        $formClass = RequestForm::class;
        $requestClass = Request::class;
        $csrf = $csrf ? '' : (PHP_EOL . '$this->removeCSRF();');
        return <<<CL
<?php

namespace {$namespace};

use {$formClass};
use {$requestClass};

/**
 * {$name} form class.
 *
 * @Form
 */
class {$name} extends Form
{

    /**
     * Calls parent method with form name and request type for this form.
     */
    public function __construct()
    {
        parent::__construct('{$formName}', Request::POST);{$csrf}
    }
CL;
    }

    /**
     * Returns class end code.
     * 
     * @return string
     */
    private function getClassEndCode(): string
    {
        return PHP_EOL . '}' . PHP_EOL;
    }

    /**
     * Not required for this console command.
     * 
     * @return boolean
     */
    protected function isClassExists()
    {
        return false;
    }

    /**
     * Not required for this console command.
     * 
     * @return array
     */
    public function getMapper()
    {
        return [];
    }

}
