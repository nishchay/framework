<?php

namespace Nishchay\Validation\Rules;

use Nishchay\Http\Request\RequestFile;

/**
 * File validation class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class FileRule extends AbstractRule
{

    /**
     * Error messages.
     * 
     * @var array
     */
    protected static $messeges = [
        'minSize' => 'Size of {field} should be aleast{0}.',
        'maxSize' => 'Size of {field} should not exceed {0}.',
        'minWidth' => 'Width of {field} should be atleast {0}.',
        'maxWidth' => 'Width of {field} should not exceed {0}.',
        'minHeight' => 'Height of {field} should be atleast {0}.',
        'maxHeight' => 'Height of {field} should not exceed {0}',
        'mime' => 'Mime of {file} should be in {0}'
    ];

    /**
     * Date rule name.
     */
    const NAME = 'file';

    /**
     * Returns name of validation type.
     * 
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * Returns TRUE if file size is greater than $size.
     * 
     * @param RequestFile $file
     * @param int $size
     * @return boolean
     */
    public function minSize(RequestFile $file, int $size): bool
    {
        return $file->getSize() >= $size;
    }

    /**
     * Returns TRUE if file size is less than $size.
     * 
     * @param RequestFile $file
     * @param int $size
     * @return boolean
     */
    public function maxSize(RequestFile $file, int $size): bool
    {
        return $file->getSize() <= $size;
    }

    /**
     * Returns dimension(width, image) of image.
     * 
     * @param RequestFile $file
     * @return boolean|array
     */
    private function getDimension(RequestFile $file)
    {
        $size = getimagesize($file->getTempName());
        if ($size === false) {
            return false;
        }

        list($width, $height) = $size;

        return [$width, $height];
    }

    /**
     * Returns TRUE if file width is greater than $width.
     * 
     * @param RequestFile $file
     * @param int $width
     * @return bool
     */
    public function minWidth(RequestFile $file, int $width): bool
    {
        if (($dimension = $this->getDimension($file)) === false) {
            return false;
        }
        list($imageWidth) = $dimension;

        return $imageWidth >= $width;
    }

    /**
     * Returns TRUE if file width is less than $width.
     * 
     * @param RequestFile $file
     * @param int $width
     * @return bool
     */
    public function maxWidth(RequestFile $file, int $width): bool
    {

        if (($dimension = $this->getDimension($file)) === false) {
            return false;
        }
        list($imageWidth) = $dimension;

        return $imageWidth <= $width;
    }

    /**
     * Returns TRUE if file height is greater than $height.
     * 
     * @param RequestFile $file
     * @param int $height
     * @return bool
     */
    public function minHeight(RequestFile $file, int $height): bool
    {
        if (($dimension = $this->getDimension($file)) === false) {
            return false;
        }
        list(, $imageHeight) = $dimension;

        return $imageHeight >= $height;
    }

    /**
     * Returns TRUE if file height is less than $height.
     * 
     * @param RequestFile $file
     * @param int $height
     * @return bool
     */
    public function maxHeight(RequestFile $file, int $height): bool
    {
        if (($dimension = $this->getDimension($file)) === false) {
            return false;
        }
        list(, $imageHeight) = $dimension;

        return $imageHeight <= $height;
    }

    /**
     * Returns true if file mime is in $mimes.
     * 
     * @param RequestFile $file
     * @param type $mimes
     * @return type
     */
    public function mime(RequestFile $file, $mimes)
    {
        if (is_string($mimes)) {
            $mimes = [$mimes];
        }

        return in_array($file->getType(), array_map('strtolower', $mimes));
    }

}
