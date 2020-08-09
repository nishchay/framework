<?php

namespace Nishchay\Lang\Loader;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Data\Query;

/**
 * DB Loader class for languages.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DBLoader implements LangLoaderInterface
{

    /**
     * Locale as table
     */
    const LOCALE_AS_TABLE = 'table';

    /**
     * Locale as column
     */
    const LOCALE_AS_COLUMN = 'column';

    /**
     * Database connection name.
     * 
     * @var string 
     */
    private $connection;

    /**
     * How locale is stored in table column or its as table itself.
     * 
     * @var string
     */
    private $localeAs;

    /**
     * Translations table name.
     * 
     * @var string
     */
    private $table;

    /**
     * Cache expiry in seconds.
     * False to disable to cache.
     * 
     * @var int 
     */
    private $cacheEpiry = 3600;

    /**
     * Returns translations of given namespace in locale.
     * 
     * @param string $namespace
     * @param string $locale
     * @return array
     */
    public function getTranslations(string $namespace, string $locale)
    {
        $query = $this->getQuery($locale)
                ->setCondition('namespace', $namespace);

        if ($this->cacheEpiry !== false) {
            $key = $this->getTableName($locale) . '_' . $locale . '_' . $namespace;
            $query->setCache($key, (int) $this->cacheEpiry);
        }

        $result = $query->get();

        $translations = [];
        foreach ($result as $row) {
            $translations[$row->key] = $row->content;
        }

        return $translations;
    }

    /**
     * Returns cache key.
     * 
     * @param string $locale
     * @param string $namespace
     * @return string
     */
    public function getCacheKey(string $locale, string $namespace): string
    {
        return $this->getTableName($locale) . '_' . $locale . '_' . $namespace;
    }

    /**
     * Returns table name where translations are stored.
     * 
     * @param string $locale
     * @return string
     */
    private function getTableName(string $locale): string
    {
        if ($this->localeAs === self::LOCALE_AS_COLUMN) {
            return $this->table;
        }

        return 'AppLang' . ucfirst($locale);
    }

    /**
     * Returns instance or Query.
     * 
     * @return Query
     */
    private function getQuery(string $locale): Query
    {
        $query = new Query($this->connection);

        $query->setTable($this->getTableName($locale));

        # Locale as column then adding condition for locale
        if ($this->localeAs === self::LOCALE_AS_COLUMN) {
            $query->setCondition('locale', $locale);
        }

        $query->setColumn([
            'key', 'content'
        ]);

        return $query;
    }

    /**
     * Initialization.
     * Loads config and set properties of this class.
     * 
     * @throws ApplicationException
     */
    public function init()
    {
        $config = Nishchay::getSetting('lang.db');
        if ($config->connection !== null && is_string($config->connection) === false) {
            throw new ApplicationException('Invalid database connection name for lang.', null, null, 921001);
        }

        # Which database connection to use.
        $this->connection = $config->connection;


        $this->localeAs = isset($config->localAs) && $config->localAs === self::LOCALE_AS_TABLE ?
                self::LOCALE_AS_TABLE : self::LOCALE_AS_COLUMN;

        # LocaleAs table then we need table name also.
        if ($this->localeAs === self::LOCALE_AS_COLUMN) {
            if (!isset($config->table) || is_string($config->table) === false) {
                throw new ApplicationException('Invalid database table name for lang.', null, null, 921002);
            }

            $this->table = $config->table;
        }

        # If cache expiry config is not there we will still use default expiry.
        if (isset($config->cacheExpiry)) {
            $this->cacheEpiry = $config->cacheExpiry;
        }
    }

}
