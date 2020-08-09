<?php

namespace Nishchay\Lang\Loader;

/**
 * File Loader class for languages.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
interface LangLoaderInterface
{

    public function init();

    public function getTranslations(string $namespace, string $locale);
}
