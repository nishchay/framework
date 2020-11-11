<?php

namespace Nishchay\Http\Request;

use Nishchay\Utility\StringUtility;

/**
 * Request File class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class RequestFile
{

    /**
     * Actual name of  file.
     * 
     * @var string
     */
    private $fileName;

    /**
     * Location to temp file where file is first .
     * 
     * @var string
     */
    private $tempName;

    /**
     * Type of file.
     * 
     * @var string
     */
    private $type;

    /**
     * Error code if any for file.
     * 
     * @var int
     */
    private $error;

    /**
     * Size of file
     * 
     * @var int
     */
    private $size;

    /**
     * Initialization.
     * 
     * @param string $fileName
     * @param string $type
     * @param string $tempName
     * @param int $error
     * @param int $size
     */
    public function __construct(string $fileName, string $type, string $tempName, int $error, int $size)
    {
        $this->setFileName($fileName)
                ->setType($type)
                ->setTempName($tempName)
                ->setError($error)
                ->setSize($size);
    }

    /**
     * Returns actual name of  file.
     * 
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Returns temp name of  file.
     * 
     * @return string
     */
    public function getTempName(): string
    {
        return $this->tempName;
    }

    /**
     * Returns type of file.
     * 
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns size of file.
     * 
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Returns error code if any error occurred while uploading file.
     * 
     * @return int
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Sets actual name of file.
     * 
     * @param string $fileName
     * @return $this
     */
    private function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * Renames uploaded file.
     * Do not pass extension.
     * 
     * @param string $newName
     */
    public function rename(string $newName)
    {
        $ext = StringUtility::getExplodeLast('.', $this->getFileName());
        $this->fileName = $newName . '.' . $ext;
        return $this;
    }

    /**
     * Sets temp name of file.
     * 
     * @param string $tempName
     * @return $this
     */
    private function setTempName(string $tempName)
    {
        $this->tempName = $tempName;
        return $this;
    }

    /**
     * Sets type of file.
     * 
     * @param string $type
     * @return $this
     */
    private function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Sets size of file.
     * 
     * @param int $size
     * @return $this
     */
    private function setSize(int $size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Error code if any.
     * 
     * @param int $error
     * @return $this
     */
    private function setError(int $error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Moves file to destination location.
     * 
     * @param string $location
     * @return bool
     */
    public function move(string $location): bool
    {
        return move_uploaded_file($this->getTempName(), $location . DS . $this->getFileName());
    }

}
