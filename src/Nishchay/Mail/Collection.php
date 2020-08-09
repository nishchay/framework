<?php

namespace Nishchay\Mail;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Processor\AbstractSingleton;

/**
 * Mail Collection
 *
 * @author bpatel
 */
class Collection extends AbstractSingleton
{

    /**
     * Name of mail configs.
     */
    const MAIL_CONFIG_LIST = 'mail.config';

    /**
     * Name of default 
     */
    const MAIL_CONFIG_DEFAULT = 'mail.default';

    /**
     * Connected mail config.
     * 
     * @var array 
     */
    private $mailInstance = [];

    /**
     *
     * @var type 
     */
    protected static $instance;

    /**
     * Returns mailer instance of given config name.
     * 
     * @param type $name
     * @return type
     */
    public function get($name = null)
    {
        # Will use default config when no name is passed.
        if ($name === null) {
            $name = Nishchay::getSetting(self::MAIL_CONFIG_DEFAULT);
        }

        # Returning already loaded mail instance
        if (array_key_exists($name, $this->mailInstance)) {
            return $this->mailInstance[$name];
        }

        $config = $this->getConfig($name);
        if ($config === false) {
            throw new ApplicationException('Mail config [' . $name . '] does not exists.', null, null, 923001);
        }
        return $this->mailInstance[$name] = new Mail($config);
    }

    /**
     * Returns config of given name.
     * 
     * @param string $name
     * @return boolean|\stdClass
     */
    private function getConfig($name)
    {
        $configList = Nishchay::getSetting(self::MAIL_CONFIG_LIST);

        if (isset($configList->{$name}) === false) {
            return false;
        }

        return $configList->{$name};
    }

    protected function onCreateInstance()
    {
        // NOTHING TO DO
    }

}
