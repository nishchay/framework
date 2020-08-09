<?php

namespace Nishchay\Security\Encrypt;

use Nishchay;
use Nishchay\Processor\AbstractSingleton;

/**
 * Description of Encrypt
 *
 * @author bpatel
 */
class Collection extends AbstractSingleton
{

    const CONFIG_NAME = 'encryption';

    /**
     * Instance of this class.
     * 
     * @var self
     */
    protected static $instance;

    /**
     * List of encryption collection.
     * 
     * @var array
     */
    private $collection = [];

    protected function onCreateInstance()
    {
        
    }

    /**
     * Returns instance of Encrypter for gi en $name.
     * 
     * @param type $name
     * @return Encrypter
     */
    public function get($name = null)
    {
        if ($name === null) {
            $name = Nishchay::getSetting(self::CONFIG_NAME . '.default');
        }

        if (array_key_exists($name, $this->collection)) {
            return $this->collection[$name];
        }

        $config = Nishchay::getSetting(self::CONFIG_NAME . '.config.' . $name);

        return $this->collection[$name] = new Encrypter($config);
    }

}
