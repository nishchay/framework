<?php

namespace Nishchay\Lang\Loader;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Lang\Loader\LangLoaderInterface;
use Nishchay\Utility\SystemUtility;
use Nishchay\Utility\Coding;

/**
 * File Loader class for languages.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class FileLoader implements LangLoaderInterface
{

    /**
     * Path to lang directory.
     * 
     * @var string 
     */
    private $langPath;

    /**
     * Sets lang path.
     * 
     * @throws ApplicationException
     */
    public function init()
    {
        $this->langPath = SystemUtility::refactorDS(Nishchay::getSetting('lang.file.path'));
        if (file_exists($this->langPath) === false) {
            throw new ApplicationException('Lang translations directory does not exists.', null, null, 921003);
        }
    }

    /**
     * Returns translations of given namespace.
     * 
     * @param string $namespace
     * @param string $locale
     * @return array
     * @throws NotSupportedException
     */
    public function getTranslations(string $namespace, string $locale)
    {
        $type = strtolower(Nishchay::getSetting('lang.file.type'));

        if (!in_array($type, ['php', 'json'])) {
            throw new NotSupportedException('Only json and php files are'
                    . ' supported for lang file.', null, null, 921004);
        }

        # Preparing path.
        $path = $this->langPath . $locale . DS .
                SystemUtility::refactorDS($namespace) . '.' . $type;
        if (file_exists($path) === false) {
            return [];
        }

        if ($type === 'php') {
            $translations = require $path;
        } else {
            $translations = Coding::decodeJSON(file_get_contents($path), true);
        }
        $straighTranslation = [];
        $this->arrayToStraight($translations, $straighTranslation);
        return $straighTranslation;
    }

    /**
     * Converts array to straight.
     * 
     * @param array $array
     * @param array $newArray
     * @param string $join
     * @param string $newKey
     * @throws ApplicationException
     */
    private function arrayToStraight($array, &$newArray, $join = '.', $newKey = '')
    {
        foreach ($array as $key => $value) {

            # Preparing current key.
            $currentKey = trim("{$newKey}{$join}{$key}", $join);
            if (is_array($value)) {
                $this->arrayToStraight($value, $newArray, $join, $currentKey);
                continue;
            } else if (!is_scalar($value)) {
                # Value other than scaler type not allowed.
                throw new ApplicationException('Invalid value for key [' . $currentKey . '].', null, null, 921005);
            }
            $newArray[$currentKey] = $value;
        }
    }

}
