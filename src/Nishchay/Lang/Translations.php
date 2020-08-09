<?php

namespace Nishchay\Lang;

use Nishchay\Exception\ApplicationException;

/**
 * Translation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Translations
{

    /**
     *
     * @var type 
     */
    private $translations = [];

    /**
     * 
     * @param type $translations
     */
    public function __construct($translations)
    {
        $this->translations = $translations;
    }

    /**
     * 
     * @param type $key
     * @param type $placeholder
     * @return type
     * @throws ApplicationException
     */
    public function line(string $key, array $placeholder = [])
    {
        if (array_key_exists($key, $this->translations) === false) {
            throw new ApplicationException('Translation of [' . $key . ']'
                    . ' does not exist.', null, null, 921009);
        }

        return $this->replacePlaceholder($this->translations[$key], $placeholder);
    }

    /**
     * Replaces placeholder in line sentence.
     * 
     * @param string $line
     * @param array $placeholder
     * @return string
     */
    private function replacePlaceholder(string $line, array $placeholder = [])
    {
        if (!is_array($placeholder)) {
            return $line;
        }

        return preg_replace_callback('#(?:@?){(\w+)}#', function ($match) use ($placeholder) {
            if (strpos($match[0], '@') === 0) {
                return substr($match[0], 1);
            }
            if (array_key_exists($match[1], $placeholder) === false) {
                return $match[0];
            }
            return $placeholder[$match[1]];
        }, $line);
    }

}
