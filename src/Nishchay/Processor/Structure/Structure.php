<?php

namespace Nishchay\Processor\Structure;

use Nishchay;
use Nishchay\Exception\InvalidStructureException;
use XMLReader;
use Nishchay\Utility\StringUtility;

/**
 * Description of Structure
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Structure
{

    /**
     * continue attribute name.
     */
    const ATTR_CONTINUE = 'continue';

    /**
     * nest attribute name.
     */
    const ATTR_NEST = 'nest';

    /**
     * require attribute name.
     */
    const ATTR_REQUIRE = 'require';

    /**
     * root attribute name for non empty only.
     */
    const ATTR_ROOT = 'root';

    /**
     * Type of file instead of special.
     */
    const FILE_TYPE_OTHER = 'other';

    /**
     * Class type of file.
     */
    const FILE_TYPE_CLASS = 'class';

    /**
     * File is intended to use as view.
     */
    const FILE_TYPE_VIEW = 'view';

    /**
     * Regex for nest attribute.
     */
    const NEST_PATTERN = '[a-zA-Z0-9./\\\\_-]*';

    /**
     * Regex for continue attribute.
     */
    const CONTINUE_PATTERN = '[a-zA-Z0-9._-]+';

    /**
     * Regex for single node for nest attribute.
     */
    const NEST_DEPTH_LIMIT_PATTERN = '^(\{([0-9R*.]+)(,)([0-9]+)\})$';

    /**
     * Regex for full restriction rule for nest attribute.
     */
    const NEST_RULE_PATTERN = '\{([R0-9,\*.]+)\}';

    /**
     * Escaped Directory separator for regular expression
     * 
     * @var string 
     */
    private static $pds = '(/|\\\\)';

    /**
     * Attribute of current processing node.
     * 
     * @var array 
     */
    private $attribbutes = [];

    /**
     * Attribute that can break algorithm located at depth level .
     * 
     * @var int 
     */
    private $nestAt = null;

    /**
     * Breaker started flag.
     * 
     * @var boolean
     */
    private $nestStarted = false;

    /**
     * Required directory.
     * 
     * @var array 
     */
    protected $childRequired = [];

    /**
     * Depth level of current processing node.
     * 
     * @var int 
     */
    private $currentDepth = 0;

    /**
     * Child number starting from one.
     * 
     * @var int 
     */
    private $childNumber = 1;

    /**
     *
     * @var type
     */
    private $depthPath = 'D0';

    /**
     * Current being processed now.
     * 
     * @var string 
     */
    private $currentNode;

    /**
     * File registry.
     * 
     * @var array 
     */
    protected $files = [];

    /**
     * Parent element of current processing node
     * 
     * @var string 
     */
    private $parent = '';

    /**
     * All registry.
     * 
     * @var array 
     */
    protected $registry = [];

    /**
     * Context.
     * 
     * @var stirng 
     */
    private $context = '';

    /**
     * These are reserved directory names.
     * These can not be used at root element of structure definition.
     * 
     * @var array 
     */ 
    private $reservedNames = [
        'extension', 'public', 'bootstrap', 'logs', 'vendor', 'settings', 'tests', 'resources', 'persisted'];

    /**
     * Root element of structure definition.
     * so the application files should be contained within this directory only.
     * 
     * @var string 
     */
    private $rootNode = '';

    /**
     * Special XML node names.
     * 
     * @var array 
     */
    private $special = [
        'controllers' => 'controller', 'controller' => 'controller',
        'views' => 'view', 'view' => 'view',
        'entities' => 'entity', 'entity' => 'entity',
        'events' => 'event', 'event' => 'event',
        'handlers' => 'handler', 'handler' => 'handler',
        'container' => 'container', 'containers' => 'container',
        'form' => 'form', 'forms' => 'form'
    ];

    /**
     * Special node found at depth.
     * 
     * @var int 
     */
    private $specialAt = NULL;

    /**
     * Current special node name.
     * 
     * @var string 
     */
    private $specialNode = '';

    /**
     * Indicates special node started
     * 
     * @var boolean 
     */
    private $specialStart = false;

    /**
     * Supported file extensions.
     * 
     * @var array 
     */
    private $supportedExtensions = [];

    /**
     * Registered views.
     * 
     * @var array 
     */
    protected $views = [];

    /**
     * XMLReader instance to structure definition file.
     * 
     * @var \XMLReader 
     */
    private $xml;

    /**
     * Node restrictions.
     * 
     * @var array
     */
    protected $restrictions = [];

    /**
     * Child.
     * 
     * @var array
     */
    private $child = [0];

    /**
     * Path to structure definition
     * @var string
     */
    private $definiitonPath;

    /**
     * 
     */
    public function __construct($definitionPath)
    {
        $this->definiitonPath = $definitionPath;
        $this->init();
        $this->process();
    }

    /**
     * 
     */
    private function init()
    {
        $this->reservedNames = array_merge($this->reservedNames, array_keys($this->special));
        $this->supportedExtensions = '(' . implode('|', Nishchay::getSupportedExtension()) . ')';
        $this->openStructureDefinition();
    }

    /**
     * Open structure definition file using XMLReader.
     * IT throws exception if structure definition file not found.
     * 
     * @return \Nishchay\Processor\Structure\Structure
     * @throws \Nishchay\Exception\InvalidStructureException
     */
    private function openStructureDefinition()
    {

        $this->xml = new XMLReader();
        if (file_exists($this->definiitonPath)) {
            $this->xml->open($this->definiitonPath);
        } else {
            throw new InvalidStructureException('Structure definition file not found.', null, null, 925012);
        }
        return $this;
    }

    /**
     * Returns all registered files.
     * 
     * @return string
     */
    protected function getFiles()
    {
        return $this->files;
    }

    /**
     * Generates Pattern and returns
     * 
     * @param   string  $name
     * @return  string
     */
    protected function getPattern($name, $empty)
    {
        $this->processAttibutes();
        $current = "D{$this->currentDepth}C{$this->childNumber}";
        $this->parent = trim($this->removeFromEnd($current), '_');
        $name = ($name !== '' ? ($name . self::$pds) : '');

        # 'continue' and 'nest' together not allowed. Continue allow current 
        # directory to have any name with moving on same depth. So nesting 
        # directory having any name can break structure.
        if ($this->isAttributeExist(self::ATTR_CONTINUE) &&
                $this->isAttributeExist(self::ATTR_NEST)) {
            throw new InvalidStructureException('[' . self::ATTR_CONTINUE .
                    '] and [' . self::ATTR_NEST . '] together can break structure'
                    . ' standard.', null, null, 925013);
        }

        # 'continue' not applicable to special node
        if (!$this->isSpecial() && (
                $this->isAttributeExist(self::ATTR_CONTINUE) &&
                (int) $this->attribbutes[self::ATTR_CONTINUE] !== 0)) {
            $continue = (int) $this->attribbutes[self::ATTR_CONTINUE];
            $restrict = $this->depthPath;
            $this->restrictions[$restrict] = [
                'validator' => 'validateContinueRestriction',
                'rule' => $continue === 1 ? 5 : (int) $continue
            ];
            $name .= '(?P<' . $restrict . '>' . self::CONTINUE_PATTERN . ')';
            unset($this->attribbutes[self::ATTR_CONTINUE]);
        } else {
            $name .= '+' . $this->currentNode . '+';
        }

        $allowed = [self::ATTR_NEST, self::ATTR_REQUIRE, self::ATTR_ROOT];

        # We here processing only allowd attributes and ignoring attributes 
        # which are not supported. We also ignoring attrbute whose value is 
        # not 1.
        foreach ($allowed as $attrbute) {
            # If attribute not set or attribute not 1
            if (!$this->isAttributeExist($attrbute) ||
                    $this->attribbutes[$attrbute] === '0') {
                continue;
            }

            switch ($attrbute) {
                case self::ATTR_NEST:
                    $nest = $this->attribbutes[self::ATTR_NEST];
                    $restrict = $this->depthPath;
                    $this->restrictions[$restrict] = [
                        'validator' => 'validateNestRestriction',
                        'rule' => $this->getNestRestriction($nest)
                    ];

                    $name .= '?(?P<' . $restrict . '>' . self::NEST_PATTERN . ')';
                    break;
                case self::ATTR_REQUIRE:
                    $this->registerChildRequired();
                    break;
                default:
                    break;
            }
        }

        return $name;
    }

    private function getNestRestriction($value)
    {
        if ($value === '1') {
            $value = '5.3';
        }

        if (preg_match('#^(\d+)$#', $value)) {
            return [
                self::ATTR_CONTINUE, $value
            ];
        } else if (preg_match('#^(\d+)\.(\d+)$#', $value, $match)) {
            $value = "{R,{$match[1]}}{R.*,{$match[2]}}";
        }
        return [
            self::ATTR_NEST, $this->getValidateNestRestriction($value)
        ];
    }

    /**
     * Returns extracted nest restriction rule after validating it.
     * 
     * @param string $ruleDefinition
     * @return array
     * @throws \Nishchay\Exception\InvalidStructureException
     */
    private function getValidateNestRestriction($ruleDefinition)
    {

        # Removing valid rule. If resulting stirng is non empty we will
        # consider that rule is invalid.
        if (!empty(preg_replace('#' . self::NEST_RULE_PATTERN .
                                '#', '', $ruleDefinition))) {
            throw new InvalidStructureException('Invalid structure rule ['
                    . $ruleDefinition . '].', null, null, 925014);
        }
        preg_match_all('#' . self::NEST_RULE_PATTERN .
                '#', $ruleDefinition, $match);
        $returning = [];
        $toMatch = ['R', '.', '*'];
        $replaceWith = ['0', '\.', '([0-9]+)'];
        foreach ($match[0] as $rule) {
            # This must match. If it does not, we will through invalid
            # structure exception.
            if (preg_match('#' . self::NEST_DEPTH_LIMIT_PATTERN .
                            '#', $rule, $matched)) {
                unset($matched[3]);
                $rule = array_slice($matched, 2);
                if (strpos($rule[0], 'R') === FALSE) {
                    $rule[0] = 'R.' . $rule[0];
                }

                # Iterating over each depth number defined in rule to check
                # for its validation.
                $ruleToValid = substr($rule[0], 2);
                if ($ruleToValid !== FALSE) {
                    foreach (explode('.', $ruleToValid) as $depth) {
                        if ($depth !== '*' && !is_numeric($depth)) {
                            throw new InvalidStructureException('Invalid'
                                    . ' nest depth limit [' . $rule[0] . '].', null, null, 925046);
                        }
                    }
                }

                # Replacing special characters to their regex form.
                $rule[0] = str_replace($toMatch, $replaceWith, $rule[0]);
                $returning[] = $rule;
            } else {
                throw new InvalidStructureException('Invalid structure rule ['
                        . $rule[0] . '].', null, null, 925015);
            }
        }
        return $returning;
    }

    /**
     * Returns root node of the structure.
     * 
     * @return string
     */
    public function getRootNode()
    {
        return $this->rootNode;
    }

    /**
     * Checks whether given attribute name exist on current processing node.
     * 
     * @param   string      $name
     * @return  boolean
     */
    public function isAttributeExist($name)
    {
        return array_key_exists($name, $this->attribbutes);
    }

    /**
     * Returns TRUE if current node is reserved name.
     * 
     * @return boolean
     */
    protected function isReservedName()
    {
        return in_array(strtolower($this->currentNode), $this->reservedNames);
    }

    /**
     * Returns TRUE if current node is special.
     * 
     * @return boolean
     */
    protected function isSpecial()
    {
        return array_key_exists(strtolower($this->currentNode), $this->special);
    }

    /**
     * Removes given name from the end of depth path.
     * 
     * @param   string      $name
     * @return  \Nishchay\Processor\Structure\Structure
     */
    private function removeFromDepthPath($name)
    {
        $this->depthPath = $this->removeFromEnd($name);
        return $this;
    }

    /**
     * Removes given name from end of path and returns it.
     * 
     * @param   string      $name
     * @return  string
     */
    private function removeFromEnd($name)
    {
        return StringUtility::removeFromEnd($name, $this->depthPath);
    }

    /**
     * Removes current child number from child array.
     * 
     */
    private function removeChildNumber()
    {
        array_pop($this->child);
        $this->childNumber = $this->getChildNumber();
    }

    /**
     * Return current child number.
     * 
     * @return string
     */
    private function getChildNumber()
    {
        end($this->child);
        return current($this->child);
    }

    /**
     * Increments current child number by 1.
     */
    private function incrementChildNumber()
    {
        if (empty($this->child)) {
            return $this->childNumber = 1;
        }
        end($this->child);
        $this->childNumber = ++$this->child[key($this->child)];
    }

    /**
     * Sets depth path.
     * 
     * @param string    $newDepth
     */
    private function setDepthPath($newDepth)
    {
        # If the curser is going inside, we will add new depth to end of depth
        # path.
        if ($this->currentDepth < $newDepth) {
            $this->depthPath .= "_D{$newDepth}";
        }
        # When current depth is greater than new depth, it means cursor has been
        # moved backward as it has done processing child. In this case we will
        # remove child name from end of depth path.
        else if ($this->currentDepth > $newDepth) {
            $this->removeFromDepthPath("D{$this->currentDepth}" .
                    "C{$this->childNumber}")->removeChildNumber();
        }

        # Now next task is to remove current child number from depth path.
        # We have here used greater or equal becuse we also have to increment
        # child number for both geater and equal case.
        if ($this->currentDepth >= $newDepth) {
            # Here we are removing only child number. Nothing will be removed 
            # in the case greater, as we must have remvoed child detail.
            $this->removeFromDepthPath("C{$this->childNumber}")
                    ->incrementChildNumber();
        } else {
            $this->child[] = $this->childNumber = 1;
        }

        $this->depthPath .= "C{$this->childNumber}";
    }

    /**
     * Returns pattern of parent node.
     */
    private function getParentPattern($nodeName)
    {
        foreach ($this->registry as $rule) {
            if ($nodeName === $rule['depth_path']) {
                return $rule['pattern'];
            }
        };

        return current($this->registry)['pattern'];
    }

    /**
     * Processes every node of structure file.
     * 
     * @param   string $name
     */
    protected function process($name = '')
    {
        while ($this->xml->read()) {
            switch ($this->xml->nodeType) {
                case XMLReader::END_ELEMENT:

                    # Setting current depth path.
                    $this->setDepthPath($this->xml->depth);
                    $this->currentNode = $this->xml->name;
                    $this->currentDepth = $this->xml->depth;

                    # We also need to change parent path.
                    $current = "D{$this->currentDepth}C{$this->childNumber}";
                    $this->parent = trim($this->removeFromEnd($current), '_');
                    $name = $this->getParentPattern($this->parent);
                    break;
                case XMLReader::ELEMENT:
                    $this->setDepthPath($this->xml->depth);
                    $empty = $this->xml->isEmptyElement;
                    $this->currentNode = $this->xml->name;
                    $this->currentDepth = $this->xml->depth;

                    if (!$empty && trim($this->xml->readInnerXml()) === '') {
                        throw new InvalidStructureException('Element [' .
                                $this->currentNode .
                                '] does not contain any children which should' .
                                ' de defined as empty.', null, null, 925016);
                    }
                    # For special making it standard followeable name. Making 
                    # first character capital while other small.
                    if ($this->isSpecial() &&
                            strtolower($this->currentNode) !== 'views') {
                        $this->currentNode = ucfirst(strtolower($this->currentNode));
                    }

                    if ($this->currentDepth === 0) {
                        $this->zeroDepthNode();
                        $current = $this->rootNode;
                    } else {
                        $current = $this->getPattern($name, $empty);
                    }
                    # If current node is special, we will  mark that special
                    # node is started and will store current depth. We will
                    # also check that this special node is not inside special
                    # node.
                    if ($this->isSpecial()) {
                        # Checking if special already been staretd. Prevening 
                        # only start of node depth is less than current node
                        # depth.
                        if ($this->specialStart &&
                                $this->specialAt < $this->currentDepth) {
                            throw new InvalidStructureException('Special'
                                    . ' directory inside special is not allowed.', null, null, 925017);
                        }

                        $this->specialStart = TRUE;
                        $this->specialAt = $this->currentDepth;

                        # Oh this is view,let's store it.
                        if (strtolower($this->currentNode) === 'views') {
                            $this->views[] = $name;
                        }
                    }
                    # We should set flag off if speicl node has been processed.
                    else if ($this->specialAt === $this->currentDepth) {
                        $this->specialStart = FALSE;
                    }

                    $this->specialNode();
                    $this->register($current);

                    # Empty node means it should contain file.
                    if ($empty) {
                        $this->registerFile($current);
                    }
                    # Non empty node is directory.
                    else {
                        if (isset($this->attribbutes['root'])) {
                            $this->registerFile($current);
                        }
                        $this->process($current);
                    }
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Processing attributes if any defined on node to validate and register.
     * 
     * @return string
     */
    protected function processAttibutes()
    {
        $this->attribbutes = [];
        while ($this->xml->moveToNextAttribute()) {
            $this->attribbutes[$this->xml->name] = $this->xml->value;
        }

        # This is to prevent continue or nest inside nest.
        if ($this->nestStarted && $this->nestAt < $this->currentDepth) {
            if ($this->isAttributeExist('continue') ||
                    $this->isAttributeExist('nest')) {
                throw new InvalidStructureException('Attribute [continue] or [nest] is not'
                        . ' allowed if its parent has [nest] attribute.', null, null, 925018);
            }
        } else if ($this->nestAt === $this->currentDepth) {
            $this->nestStarted = false;
        }

        # Marking that node with nest attribute has been started.
        if ($this->isAttributeExist('nest')) {
            $this->nestStarted = true;
            $this->nestAt = $this->currentDepth;
        }
    }

    /**
     * Register pattern
     * 
     * @param string $pattern
     */
    protected function register($pattern)
    {
        $this->registry[] = [
            'pattern' => $pattern,
            'special' => $this->specialNode,
            'depth_path' => $this->depthPath
        ];
    }

    /**
     * Registers for directory to must have given type of file
     * 
     */
    protected function registerChildRequired()
    {
        $this->childRequired[$this->parent][] = $this->currentNode;
    }

    /**
     * Register it to validate the structure
     * 
     * @param string $pattern
     */
    protected function registerFile($pattern)
    {
        $pattern .= self::$pds . '(?P<file>[a-zA-Z0-9\._-]+)\.'
                . $this->supportedExtensions;
        $this->files[] = [
            'special' => $this->specialNode,
            'node' => $this->currentNode,
            'pattern' => $pattern,
            'depth_path' => $this->depthPath
        ];
    }

    /**
     * Set current special node.
     */
    protected function specialNode()
    {
        if ($this->isSpecial()) {
            $this->specialNode = $this->special[strtolower($this->currentNode)];
        } else if ($this->specialStart === false) {
            $this->specialNode = isset($this->attribbutes['type']) ?
                    strtolower($this->attribbutes['type']) :
                    self::FILE_TYPE_OTHER;
        }
    }

    /**
     * Processing for first element of structure definition
     * 
     * @throws Exception
     */
    protected function zeroDepthNode()
    {
        if ($this->isReservedName()) {
            throw new InvalidStructureException('[' . $this->currentNode . '] is'
                    . ' reserverd word and you can use it as starting point.', null, null, 925019);
        } else {
            $this->rootNode = $this->currentNode;
        }
    }

}
