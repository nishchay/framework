<?php

namespace Nishchay\XML;

use XMLReader;
use Nishchay\Utility\Coding;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * XML class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class XML
{

    use MethodInvokerTrait;

    /**
     *
     * @var type 
     */
    private $xmlReader;

    /**
     *
     * @var type 
     */
    private $elements;

    /**
     * Initialization
     */
    public function __construct($file)
    {
        $this->xmlReader = new XMLReader();
        $this->xmlReader->open($file);
        $this->elements = $this->process();
    }

    /**
     * 
     * @return array|NULL
     */
    public function getAttirbutes()
    {
        $attrib = [];
        while ($this->xmlReader->moveToNextAttribute()) {
            $attrib[$this->xmlReader->name] = $this->xmlReader->value;
        }

        return empty($attrib) ? NULL : $attrib;
    }

    /**
     * Processes each node of xml file to generate array.
     * 
     * @return array
     */
    protected function process()
    {
        $element = [];

        while ($this->xmlReader->read()) {
            switch ($this->xmlReader->nodeType) {
                case XMLReader::END_ELEMENT:
                    return $element;
                case XMLReader::ELEMENT:
                    $node = [];

                    $node['attrib'] = $this->getAttirbutes();

                    if (!$this->xmlReader->isEmptyElement) {
                        $value = $this->process();
                        $node['node'] = $value;
                    }

                    #If the node name with same already been processed,
                    #then we should convert already stored into array to add this node.
                    if (array_key_exists($this->xmlReader->name, $element)) {
                        if (array_key_exists('attrib', $element[$this->xmlReader->name])) {
                            $existing = $element[$this->xmlReader->name];
                            unset($element[$this->xmlReader->name]);
                            $element[$this->xmlReader->name][] = $existing;
                        }

                        $element[$this->xmlReader->name][] = $node;
                    } else {
                        $element[$this->xmlReader->name] = $node;
                    }


                case XMLReader::TEXT;
                    if (trim($this->xmlReader->value) !== '') {
                        $element = trim($this->xmlReader->value);
                    }

                    break;
                default:
                    break;
            }
        }

        return $element;
    }

    /**
     * Converts Opened XML file into array of objects
     */
    public function toObject()
    {
        return Coding::decodeJSON(Coding::encodeJSON($this->elements));
    }

    /**
     * Converts Opened XML file into array
     */
    public function toArray()
    {
        return $this->elements;
    }

    /**
     * 
     * @param   string  $name
     * @param   array   $arguments
     */
    public function __call($name, $arguments)
    {
        if ($this->isCallbackExist([$this->xmlReader, $name])) {
            return $this->invokeMethod([$this->xmlReader, $name], $arguments);
        }
    }

}
