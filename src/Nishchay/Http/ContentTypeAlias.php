<?php

namespace Nishchay\Http;

/**
 * Class for Alias of content type.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class ContentTypeAlias
{

    /**
     * Short names of all content types.
     * 
     * @var     array 
     */
    private static $contentTypeAlias = array(
        'html' => 'text/html',
        'plain' => 'text/plain',
        'text' => 'text/plain',
        'js' => 'application/javascript',
        'javascript' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'text/xml',
        'png' => 'image/png',
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
    );

    public static function getContentType($alias)
    {
        $aliasLowered = strtolower($alias);
        if (array_key_exists($aliasLowered, self::$contentTypeAlias)) {
            return self::$contentTypeAlias[$aliasLowered];
        }

        return $alias;
    }

}
