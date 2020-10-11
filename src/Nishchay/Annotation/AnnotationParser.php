<?php

namespace Nishchay\Annotation;

use Exception;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Utility\Coding;

/**
 * Annotation parser
 * This class only parses annotation
 * Validation of annotation can be found in their own class.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class AnnotationParser
{

    /**
     * Annotation regex
     * Helps finding annotation defined.
     */
    const ANNOTATIONS = '#@(.*?)(\r\n|\n)#';

    /**
     * Definition.
     * Helps parse annotation definition.
     */
    const DEFINITION = '#@(\w+)(.*)#i';

    /**
     * Variable name.
     * To check annotation name or parameter key validation.
     */
    const VAR_NAME = '#^[A-Za-z]\w*$#';

    /**
     * Start limiter of annotation or array
     */
    const START = '(';

    /**
     * Close Limiter of annotation or array
     */
    const CLOSE = ')';

    /**
     * Separator literal of annotation parameter or array values
     */
    const SEPERATOR = ',';

    /**
     * Assignment operator of value or key in parameter
     */
    const ASSIGN = '=';

    /**
     * array keyword to store array in key of annotation parameter 
     */
    const KEYWORD_ARRAY = 'array';

    /**
     * Big bracket start.
     */
    const BIG_BRACKET_START = '[';

    /**
     * Big bracket end;
     */
    const BIG_BRACKET_END = ']';

    /**
     * String enclosed either by single quote(') or double quote(")
     */
    const STRING_ENCLOSER = '\'\"';

    /**
     * Annotations which does not support parameter. Documentational annotation 
     * which are used for generating document does not have parameter. By 
     * adding name of those annotation which are usable for Nishchay,parser 
     * prevents it from ignored.
     * 
     * @var array 
     */
    private $usable = [
        'controller', 'service', 'doc', 'event', 'secure',
        'reflective', 'identity', 'handler', 'noservice','container'
    ];

    /**
     * Annotation which can be used multiple times.
     * 
     * @var array 
     */
    private $multiple = ['beforechange', 'afterchange', 'validation'];

    /**
     * Current processing annotation name.
     * 
     * @var string 
     */
    private $currentProcessing;

    /**
     * Processes each annotation to convert parameter definition into array.
     * 
     * @param   string      $name
     * @param   string      $annotation
     * @param   array       $annotations
     * @return  boolean
     */
    private function processAnnotation($name, $annotation, &$annotations)
    {
        if (is_array($annotation)) {
            # We need this variable to store all annotations. Problem here is
            # that $annotations always gets definition at $name index which
            # makes annotation to be single, but we need all defined annotation.
            # To make it possible we store each returned annotation to this
            # variable and will also remvoe $name index from this variable as 
            # this method always adds annotation to third parameter of this 
            # method.
            $returning = [];
            foreach ($annotation as $key => $definition) {
                $this->processAnnotation($name, $definition, $returning);
                if (array_key_exists($name, $returning) === false) {
                    continue;
                }
                $returning[$key] = $returning[$name];
                unset($returning[$name]);
            }

            $annotations[$name] = $returning;
            return true;
        }
        $this->currentProcessing = "@{$name}$annotation";

        # Annotation with no braces returns false.
        $token = $this->getToken($annotation);

        if ($token === false) {
            return false;
        }
        $value = $this->parse($token);
        if ($value === false && !in_array($name, $this->usable)) {
            unset($annotations[$name]);
            return true;
        }

        $annotations[$name] = $value;
        return true;
    }

    /**
     * Returns Nishchay annotations defined in documentational comment.
     * Annotation with no parameter and it's not usable in Nishchay are ignored.
     * 
     * @param   string      $doc
     * @return  boolean
     */
    public function getAnnotations($doc)
    {
        $annotations = $this->parseDoc($doc);

        if (is_array($annotations)) {
            foreach ($annotations as $key => $current) {

                # Returns in the case if there are no parameter defined for
                # annotation.
                if ($this->processAnnotation($key, $current, $annotations)) {
                    continue;
                }

                # Ignore annotaton which are not used in Nishchay.
                if (!in_array($key, $this->usable)) {
                    unset($annotations[$key]);
                } else {
                    # Denotes that this annotation does not have any parameter.
                    $annotations[$key] = false;
                }
            }
            return $annotations;
        }

        return false;
    }

    /**
     * PHP token parser does our job by validating and returns
     * parsed annotation into array.
     * 
     * @param   string          $annotation
     * @return  array|boolean
     */
    private function getToken($annotation)
    {
        $token = token_get_all('<?php ' . trim($annotation) . '?>');

        # Removing php tag.
        ArrayUtility::compact($token);

        if (current($token) === self::START) {
            $endToken = end($token);

            if ($endToken !== self::CLOSE) {
                throw new InvalidAnnotationExecption('Invalid parameter'
                        . $this->currentProcessing);
            } else {
                reset($token);

                # Now we don't want start and end values because it always
                # are enclosing operator.
                ArrayUtility::compact($token);
                return $token;
            }
        }
        return false;
    }

    /**
     * Parsing documentation comment to find Nishchay supported within it.
     * 
     * @param   string      $comment
     * @return  boolean|array
     */
    private function parseDoc($comment)
    {
        $annotations = [];
        preg_match_all(self::ANNOTATIONS, $comment, $annotations);

        # No annotation found!
        if (count($annotations) === 0) {
            return false;
        }
        return $this->parseDefinition($annotations[0]);
    }

    /**
     * Parsing annotation definition one by one and setting into array to return.
     * 
     * @param   array       $annotations
     * @return  array
     */
    private function parseDefinition($annotations)
    {
        foreach ($this->makeProper($annotations) as $key => $current) {
            # Splitting definition into annotation name and it's parameter.
            preg_match(self::DEFINITION, $current, $extracted);

            # Assiging to variable as used at many place.
            $definition = trim($extracted[2]);
            $start = substr($definition, 0, 1);

            if ($start === self::START &&
                    substr($definition, -1, 1) !== self::CLOSE) {
                continue;
            }

            # Making it case insensitive by changing all character to lower.
            $name = strtolower($extracted[1]);

            # Some annotation are allowed to be define more than one time.
            # Normal annotaiton has direct definition to it but for multiple type
            # it is stored in array.
            if (in_array($name, $this->multiple)) {
                $annotations[$name][] = $definition;
            } else {
                $annotations[$name] = $definition;
            }
            unset($annotations[$key]);
        }
        return $annotations;
    }

    /**
     * Makes annotations proper. It converts annotation defination defined on
     * multiple line to single line.
     * 
     * @param array $annotations
     * @return array
     */
    private function makeProper($annotations)
    {
        $previous = 0;
        foreach ($annotations as $key => $value) {
            if (strpos($value, '@+') === 0) {
                $annotations[$previous] = preg_replace('(\r\n|\n)', '', $annotations[$previous]) . substr($value, 2);
                unset($annotations[$key]);

                continue;
            }
            $previous = $key;
        }
        return $annotations;
    }

    /**
     * Parses single annotation at time.
     * 
     * @param   array           $token
     * @return  null
     * @throws  Exception
     */
    private function parse($token)
    {
        $params = [];
        $current = current($token);

        if ($current === false) {
            return false;
        }

        do {
            # Some token values are array but we need only string.
            if (is_array($current)) {
                $current = $current[1];
            }

            if (empty(trim($current))) {
                continue;
            }

            # Annotaiton parameter must follow naming standard.
            if (!preg_match(self::VAR_NAME, $current)) {
                throw new InvalidAnnotationExecption('Value [' . $current .
                        '] of [' . $this->currentProcessing . '] is invald');
            }

            # If parameter key have value.
            if (next($token) === self::ASSIGN) {
                $value = next($token);
                if (!is_array($value) && $value !== self::BIG_BRACKET_START) {
                    throw new InvalidAnnotationExecption('Invalid key for [' .
                            $this->currentProcessing . '] annotation');
                }

                $value = is_array($value) ? $value[1] : $value;
                if ($value === self::KEYWORD_ARRAY ||
                        $value === self::BIG_BRACKET_START) {
                    $value === self::KEYWORD_ARRAY && next($token);
                    $params[$current] = $this->getArrayValue($token, $value);
                } else {
                    $params[$current] = $this->toActualType($value);
                }
            } else {
                $params[$current] = NULL;
                prev($token);
            }

            $next = next($token);

            # At this point next token either should exists or if
            # it's exist it have to seperator.
            if ($next !== self::SEPERATOR && $next !== false) {
                throw new InvalidAnnotationExecption('Invalid [' .
                        $this->currentProcessing . '] annotation. Might be'
                        . ' invalid seperator');
            }
        } while ($current = next($token));

        return $params;
    }

    /**
     * Parsing array value.
     * 
     * @param   array           $token
     * @return  array
     * @throws  Exception
     */
    private function getArrayValue(&$token, $matched)
    {
        $current = next($token);
        $value = [];

        $arrayClose = $matched === self::BIG_BRACKET_START ?
                self::BIG_BRACKET_END : self::CLOSE;
        do {
            if (is_array($current)) {
                $current = $current[1];
            }

            $next = next($token);

            if ($next === self::ASSIGN) {
                $array_value = next($token);
                if (is_array($array_value)) {
                    $array_value = $array_value[1];
                }

                if ($array_value === self::SEPERATOR) {
                    goto PARSE_ERROR;
                }

                $value[$current] = $this->toActualType($array_value);
                $next = next($token);
            } else {
                $value[] = $this->toActualType($current);
            }

            if ($next === $arrayClose) {
                break;
            } else if ($next !== self::SEPERATOR) {
                PARSE_ERROR:
                throw new InvalidAnnotationExecption('Invalid array definition'
                        . ' for [' . $this->currentProcessing . '] annotation.');
            }
        } while (($current = next($token)) !== $arrayClose);

        return $value;
    }

    /**
     * Transforms value to their actual data type if not enclosed within quotation.
     * 
     * @param   string      $value
     * @return  mixed 
     */
    protected function toActualType($value)
    {
        $value = Coding::toActualType($value);

        if (is_bool($value) || is_null($value)) {
            return $value;
        } else {
            return trim($value, self::STRING_ENCLOSER);
        }
    }

    /**
     * Returns description from doc comment.
     * 
     * @param string $comment
     * @return string
     */
    public function getDescription($comment)
    {
        $lines = preg_split("/(\r?\n)/", $comment);
        ArrayUtility::compact($lines);
        $description = [];
        foreach ($lines as $line) {
            $line = preg_replace('/^([\*|\s]+)/', '', $line);
            if (empty($line) || strpos($line[0], '@') !== 0) {
                $description[] = $line;
            } else {
                break;
            }
        }
        return trim(implode(PHP_EOL, $description));
    }

    /**
     * Returns annotaiton definition as string.
     * 
     * @param string $doc
     * @return array
     */
    public function getAnnotationAsString($doc)
    {
        $annotations = $this->parseDoc($doc);

        foreach ($annotations as $name => $parameter) {
            # Annotation with no braces returns false.
            $token = $this->getToken($parameter);
            $value = false;

            if ($token !== false) {
                $value = $this->parse($token);
            }

            if ($value === false && !in_array($name, $this->usable)) {
                unset($annotations[$name]);
                continue;
            }
        }
        return $annotations;
    }

}
