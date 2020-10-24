<?php

namespace Nishchay\Generator;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Generator\Skelton\Entity\EmptyEntity;
use Nishchay\Generator\Skelton\Entity\CrudEntity;
use Nishchay\Utility\Coding;
use Nishchay\Generator\Skelton\Entity\TemplateMapper;
use Nishchay\Console\Printer;
use Nishchay\Data\Annotation\Property\DataType;
use Nishchay\FileManager\SimpleFile;
use Nishchay\Utility\SystemUtility;
use Nishchay\Processor\VariableType;
use Nishchay\Data\Meta\MetaTable;
use Nishchay\Data\Meta\MetaConnection;
use Nishchay\Data\Annotation\Property\Property;

/**
 * Entity Generator class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Entity extends AbstractGenerator
{

    const MAPPING = [
        'smallint' => VariableType::INT,
        'mediumint' => VariableType::INT,
        'integer' => VariableType::INT,
        'serial' => VariableType::INT,
        'bigint' => VariableType::INT,
        'int' => VariableType::INT,
        'char' => VariableType::STRING,
        'binary' => VariableType::STRING,
        'varbinary' => VariableType::STRING,
        'tinyblob' => VariableType::STRING,
        'tinytext' => VariableType::STRING,
        'blob' => VariableType::STRING,
        'mediumtext' => VariableType::STRING,
        'mediumblob' => VariableType::STRING,
        'longtext' => VariableType::STRING,
        'varchar' => VariableType::STRING,
        'text' => VariableType::STRING,
        'enum' => VariableType::STRING,
        'set' => VariableType::STRING,
        'tinyint' => VariableType::BOOLEAN,
        'bit' => VariableType::BOOLEAN,
        'bool' => VariableType::BOOLEAN,
        'boolean' => VariableType::BOOLEAN,
        'datetime' => VariableType::DATETIME,
        'date' => VariableType::DATE,
        'datetime' => VariableType::DATETIME,
        'time' => VariableType::DATE,
        'timestamp' => VariableType::DATETIME,
        'year' => VariableType::INT,
        'dec' => VariableType::DOUBLE,
        'decial' => VariableType::DOUBLE,
        'float' => VariableType::DOUBLE,
        'double' => VariableType::DOUBLE
    ];

    /**
     * Regex for property name and class name.
     * 
     */
    const REGEX = '#^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$#';

    public function __construct($name)
    {
        parent::__construct($name, 'entity');
    }

    /**
     * Returns Template mapper for entity.
     * 
     * @return TemplateMapper
     */
    public function getMapper(): TemplateMapper
    {
        if ($this->templateMapper !== null) {
            return $this->templateMapper;
        }

        return $this->templateMapper = new TemplateMapper();
    }

    /**
     * Returns true if class with name already exists.
     * 
     * @return type
     */
    protected function isClassExists()
    {
        return Nishchay::getEntityCollection()->isExist($this->name);
    }

    /**
     * Creates empty entity class with only identity property in it.
     * 
     * @return type
     */
    public function createEmpty()
    {
        return $this->createClass(EmptyEntity::class, [$this, 'writeIdentityId']);
    }

    /**
     * Creates entity class with CRUD related properties.
     * 
     * @return string
     */
    public function createCrud()
    {
        return $this->createClass(CrudEntity::class, [$this, 'writeIdentityId']);
    }

    /**
     * 
     * @param type $name
     * @return type
     */
    private function isValidPropOrClassName($name)
    {
        return preg_match(self::REGEX, $name);
    }

    /**
     * Generates new entity by asking questions.
     * 
     * @return boolean
     */
    public function createNew()
    {

        $namespace = $this->getNamespace();

        $entity = $this->getInput('Enter entity name', null);

        if (!$this->isValidPropOrClassName($entity)) {
            Printer::red('Entity name is not valid.' . PHP_EOL);
            return false;
        }

        Printer::green('Now we will keep asking for property(Type q then enter to stop)' . PHP_EOL);
        Printer::green('First property will be consdered as int type and identity property of entity' . PHP_EOL);

        $properties = [];
        $dataType = [];
        foreach (DataType::PREDEFINED_TYPES as $index => $value) {
            $dataType[$index + 1] = $value;
        }

        while (true) {

            # Asking user for property name.
            $property = $this->getInput('Enter property name(Type q then enter to stop)', null);
            if (strtolower($property) === 'q') {
                break;
            }

            # Checking if provided property name is valid.
            if (!$this->isValidPropOrClassName($property)) {
                Printer::red('Property name is not valid.' . PHP_EOL);
                return false;
            }

            $identity = false;

            # For the first property we taking its data type as int and making it
            # identity property.
            if (!empty($properties)) {
                $chosenDataType = $this->getInput('Select Data type(Type number)', $dataType, 3, true);
                if (isset($dataType[$chosenDataType])) {
                    $chosenDataType = $dataType[$chosenDataType];
                } else {
                    Printer::yellow('Invalid data type choice enter, we have'
                            . 'applied data type string for this property.');
                    $chosenDataType = VariableType::STRING;
                }
            } else {
                $chosenDataType = VariableType::INT;
                $identity = true;
            }
            $properties[] = [$property, ['type=' . $chosenDataType], $identity];
        }

        if (empty($properties)) {
            Printer::red('Entity without property is not allowed.' . PHP_EOL);
            return false;
        }

        $this->createEntity($namespace, $entity, $entity, $properties);
    }

    /**
     * Create entity from table.
     * 
     * @param string|null $namespace
     */
    public function createFromTable(?string $namespace = null, ?string $connection = null)
    {
        # Fetching columns first so that if connecton is offline or we are not
        # able to connect to database, we can then terminate instantly.
        $meta = new MetaTable($this->name, $connection);
        $columns = $meta->getColumns();

        if ($namespace === null) {
            $namespace = $this->getNamespace();
        }

        # This will be used as class name so converting its first character to
        # uppercase.
        $entity = ucfirst($this->name);

        $properties = [];
        foreach ($columns as $row) {
            if ($row->name === Property::EXTRA_PROPERTY) {
                continue;
            }
            $type = ['type=' . self::MAPPING[strtolower($row->dataType)]];
            if ($row->maxLength > 0) {
                $type[] = 'length=' . $row->maxLength . ($row->scale ? ('.' . $row->scale) : '');
            }

            if (strtolower($row->nullable) === 'no') {
                $type[] = 'required=true';
            }
            $properties[] = [$row->name, $type, $row->primaryKey];
        }

        $this->createEntity($namespace, $entity, $this->name, $properties);
    }

    /**
     * Creates entities of all tables found in DB.
     * 
     * @return bool
     */
    public function createFromDB(?string $connection = null): bool
    {
        # Fetching tables first so that if connecton is offline or we are not
        # able to connect to database, we can then terminate instantly.
        $meta = new MetaConnection($connection);
        $tables = $meta->getTables();

        $all = $this->getInput('Do you want to create entities of all tables?', 'YN') === 'y';

        $namespace = $this->getNamespace();

        foreach ($tables as $table) {

            if ($all === false) {
                if ($this->getInput('Do you want to create [' . $table->tableName . ']', 'YN') !== 'y') {
                    continue;
                }
            }

            $this->name = $table->tableName;
            $this->createFromTable($namespace, $connection);
        }

        return true;
    }

    /**
     * 
     * @param \Nishchay\Generator\stirng $namespace
     * @param string $entity
     * @param array $properties
     */
    private function createEntity(string $namespace, string $entity, string $table, array $properties)
    {
        $filePath = SystemUtility::refactorDS(ROOT . $namespace . DS . $entity . '.php');

        if (empty($properties)) {
            Printer::red('Can not create:' . $entity . '. Table or columns not found.' . PHP_EOL);
            return false;
        }
        $this->name = $namespace . '\\' . $entity;

        # Validating file.
        try {
            $this->isValidFile(false);
        } catch (ApplicationException $e) {
            Printer::red('[' . $this->name . '] is already exists.' . PHP_EOL);
            return null;
        }
        $file = new SimpleFile($filePath, SimpleFile::TRUNCATE_WRITE);

        # Writing start of class.
        $file->write($this->getClassStartCode($namespace, $entity, $table));

        # Iterating over each properties and write it to class.
        foreach ($properties as $property) {
            $file->write(PHP_EOL . $this->getPropertyCode(...$property) . PHP_EOL);
        }

        # Ending class
        $file->write($this->getClassEndCode());

        # Close
        $file->close();

        # Inform
        Printer::write('Created at ');
        Printer::yellow($filePath . PHP_EOL);
    }

    /**
     * Returns class start code.
     * 
     * @param string $namespace
     * @param string $name
     * @return string
     */
    private function getClassStartCode(string $namespace, string $name, string $table): string
    {

        $table = $name === $table ? 'this.base' : $table;
        return <<<CL
<?php

namespace {$namespace};

/**
 * @Entity(name='{$table}')
 */
class {$name}
{

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
     * Returns property code.
     * 
     * @param string $name
     * @param string $type
     * @return string
     */
    private function getPropertyCode(string $name, array $type, bool $identity): string
    {
        $identity = $identity ? '@Identity' : '';
        $type = implode(',', $type);
        return <<<PROP
    /**
     * {$identity}   
     * @DataType({$type})
     */
    public \${$name};
PROP;
    }

    /**
     * Renames identityId property with property name as per new entity class name.
     * 
     * @param string $content
     * @return string
     */
    protected function writeIdentityId(string $content): string
    {
        return str_replace('identityId', lcfirst(Coding::getClassBaseName($this->name)) . 'Id', $content);
    }

}
