<?php

namespace Nishchay\Processor\Structure;

use Nishchay;
use Nishchay\Exception\InvalidStructureException;
use ViewCollection;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Http\Request\Request;

/**
 * Structure Processor for the code.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class StructureProcessor extends Structure
{

    /**
     * Escaped ROOT path for regular expression
     * 
     * @var string 
     */
    private $pregRoot = ROOT;

    /**
     *
     * @var array 
     */
    private $restrictedData = [];

    /**
     * Previous matching restriction rule.
     * 
     * @var array 
     */
    private $previousMatching = [];

    /**
     * Valid controller and entity directory.
     * 
     * @var array
     */
    private $directorires = [];

    /**
     * Initialization
     * 
     */
    public function __construct($definitionPath)
    {
        parent::__construct($definitionPath);
        $this->init();
    }

    /**
     * Initialization
     */
    private function init()
    {
        $this->pregRoot = preg_quote(ROOT);

        # Setting global APP path constant
        defined('APP') || define('APP', ROOT . $this->getRootNode());
    }

    /**
     * Validates given directory $path with the defined structure.
     * Also checks child required
     *  
     * @param   string                      $path       path to directory
     * @return  string                                  Type of file
     * @throws  InvalidStructureException
     */
    public function isValidDirectory($path)
    {
        $message = '';
        foreach ($this->registry as $value) {
            $pattern = '#^' . $this->pregRoot . $value['pattern'] . '$#';

            if (preg_match($pattern, $path, $match)) {

                if (($message = $this->validateRestriction($match)) !== true) {
                    break;
                }

                if ($value['special'] === 'views') {
                    ViewCollection::store($path);
                }

                if (in_array($value['special'], ['controller', 'entity'])) {
                    $this->directorires[$value['special']][str_replace([ROOT, DS], ['','\\'], $path)] = $path;
                }

                $this->processRequiredDirectory($path, $value['depth_path']);
                return $value['special'];
            }
        }

        throw new InvalidStructureException('Path [' . $path . '] is not valid as'
                . ' per structure definition. ' . $message, null, null, 925020);
    }

    /**
     * Returns directories of given type.
     * 
     * @param string $type
     * @return array
     */
    public function getDirectories($type): array
    {
        return $this->directorires[$type] ?? [];
    }

    /**
     * Processes files if there is any child required under given $path
     * 
     * @param   string  $path
     * @param   string  $directory
     * @return  boolean
     * @throws  InvalidStructureException
     */
    private function processRequiredDirectory($path, $directory)
    {
        # Will skip required directory to be check if create command is
        # executed from console command while application stage in test or local.
        if ($this->isCreateCommand()) {
            return true;
        }

        if (!array_key_exists($directory, $this->childRequired)) {
            return true;
        }

        $detail = $this->childRequired[$directory];

        #Iterate all the registered required child
        foreach ($detail as $required) {
            if (!file_exists($path . DS . $required)) {
                throw new InvalidStructureException('[' . $path . DS . $required .
                        '] is required as per structure definition.', null, null, 925021);
            }
        }
        unset($this->childRequired[$directory]);
        return true;
    }

    /**
     * Returns TRUE if create command executed while not in live stage.
     * 
     * @return boolean
     */
    private function isCreateCommand()
    {
        if (Nishchay::isApplicationStageLive() === false && Nishchay::isApplicationRunningForCommand()) {

            $arguments = Request::server('argv');
            if (isset($arguments[2]) && $arguments[2] === '-create') {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks file is valid as per defined structure.
     * 
     * @param   string                      $file
     * @return  array
     * @throws  InvalidStructureException
     */
    public function isValidFile($file)
    {
        foreach ($this->files as $value) {
            $pattern = '#^' . $this->pregRoot . $value['pattern'] . '$#';
            if (preg_match($pattern, $file, $match)) {
                return $value;
            }
        }

        throw new InvalidStructureException('File [' . $file . '] is not valid '
                . 'as per structure definition.', null, null, 925022);
    }

    /**
     * Iterates over each matched directory or file to validate against
     * restriction defined within structure definition.
     * 
     * @param array $matched
     */
    private function validateRestriction($matched)
    {
        foreach ($matched as $key => $value) {
            if (is_numeric($key) || empty($value)) {
                continue;
            }

            return $this->validateRule($this->getRestrictionRule($key), $key,
                            $value);
        }
        return true;
    }

    /**
     * Returns restriction validate rule if it is defined for given name.
     * 
     * @param   string    $name
     * @return  array|boolean
     */
    private function getRestrictionRule($name)
    {
        return isset($this->restrictions[$name]) ?
                $this->restrictions[$name] : false;
    }

    /**
     * 
     * @param   array       $rule
     * @param   string      $key
     * @param   string      $value
     * @return  boolean
     */
    private function validateRule($rule, $key, $value)
    {
        if ($rule === false) {
            return true;
        }

        $value = trim($value, DS);
        !isset($this->restrictedData[$key]) && $this->restrictedData[$key] = [
        ];
        $this->storeAsTree($this->restrictedData[$key], explode(DS, $value));
        return call_user_func_array([$this, $rule['validator']],
                [
                    $rule['rule'], $key, $value
        ]);
    }

    /**
     * Stores path as tree.
     * 
     * @param array $array
     * @param type $store
     * @return type
     */
    private function storeAsTree(&$array, $store)
    {
        if (empty($store)) {
            return;
        }
        foreach ($store as $val) {
            isset($array[$val]) === false && $array[$val] = [];
            array_shift($store);
            return $this->storeAsTree($array[$val], $store);
        }
    }

    /**
     * Validates continue restriction.
     * 
     * @param type $limit
     * @param type $key
     * @throws InvalidStructureException
     */
    private function validateContinueRestriction($limit, $key)
    {
        if (count($this->restrictedData[$key]) > $limit) {
            return 'Invalid structure as continue is limited to ' .
                    $limit . ' only and you have created [' .
                    implode(',', array_keys($this->restrictedData[$key])) .
                    '] directories';
        }
        return true;
    }

    /**
     * Returns child count should be as per restriction rule.
     * 
     * @param   array   $rule
     * @param   int     $depth
     * @param   string  $name
     * @return  int
     */
    private function getChildCountShouldBe($rule, $name)
    {
        foreach ($rule as $rowRule) {
            if (preg_match('#^' . $rowRule[0] . '$#', $name)) {
                return $rowRule[1];
            }
        }
        return false;
    }

    /**
     * 
     * @param type $rule
     * @param type $key
     * @param type $value
     * @return type
     */
    private function validateNestRestriction($rule, $key, $value)
    {
        if ($rule === false) {
            return true;
        }
        if ($rule[0] === Structure::ATTR_CONTINUE) {
            return $this->validateContinueRestriction($rule[1], $key);
        } else {
            $name = $this->getTreeName($key, explode(DS, $value));
            $shouldBe = $this->getChildCountShouldBe($rule[1], $name);

            if ($shouldBe === false) {
                return 'Child not allowed';
            }

            if ($this->getChildCount($key, $value) > $shouldBe) {
                return 'Number of child exceed then allowed'
                        . ' limit [' . $shouldBe . '].';
            }
            return true;
        }
    }

    /**
     * Returns count of number of child in given directory.
     * 
     * @param type $directory_name
     * @param type $diretory
     * @return type
     */
    private function getChildCount($directory_name, $diretory)
    {
        $child = $this->restrictedData[$directory_name];
        $tree = explode(DS, $diretory);
        array_pop($tree);
        foreach ($tree as $name) {
            $child = $child[$name];
        }
        return count($child);
    }

    /**
     * Returns tree name.
     * 
     * @param type $key
     * @param type $directory
     * @return type
     */
    private function getTreeName($key, $directory)
    {
        $pos = '0';
        $array = $this->restrictedData[$key];
        array_pop($directory);
        foreach ($directory as $value) {
            $pos .= '.' . ArrayUtility::getKeyPosition($value, $array);
            $array = $array[$value];
        }
        return trim($pos, '.');
    }

}
