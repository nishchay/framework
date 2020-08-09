<?php

namespace Nishchay\Session\SaveHandler;

use Nishchay;
use Nishchay\Processor\InternalSession;
use Nishchay\Session\TillNextRequestCount;
use Nishchay\Utility\Coding;

/**
 * Session save handler
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class SaveHandler
{

    /**
     * Session Data.
     * 
     * @var array 
     */
    protected static $data = [];

    /**
     * Session save handler.
     * 
     * @var \Nishchay\Session\SaveHandler\Handlers 
     */
    private $handler = false;

    /**
     * Session ID.
     * 
     * @var string 
     */
    private $sessionId;

    /**
     * Flag to indicate whether session handler set.
     * 
     * @var boolean
     */
    private static $isHandlerSet = false;

    /**
     * Initializes session save handler.
     */
    protected function initHandler()
    {
        $this->setHandler()->register();
    }

    /**
     * Sets configured session save handler.
     * 
     * @return \Nishchay\Session\SaveHandler\SaveHandler
     */
    private function setHandler()
    {
        $config = Nishchay::getConfig('session');
        $this->handler = new Handlers(strtolower($config->storage));
        return $this;
    }

    /**
     * Registers session save handler.
     */
    private function register()
    {
        if (self::$isHandlerSet) {
            return;
        }
        self::$isHandlerSet = true;
        session_set_save_handler(
                [$this, 'open'], [$this, 'close'], [$this, 'read'],
                [$this, 'write'], [$this, 'destroy'], [$this, 'gc']);
    }

    /**
     * Executed on session start.
     * 
     * @param string $save_path
     * @param string $name
     * @return boolean  TRUE on success otherwise FALSE.
     */
    public function open($save_path, $session_name)
    {
        return $this->handler
                        ->getHandler()
                        ->open($save_path, $session_name);
    }

    /**
     * Executed on session close.
     * 
     * @return boolean TRUE on success otherwise FALSE.
     */
    public function close()
    {
        return $this->handler
                        ->getHandler()
                        ->close();
    }

    /**
     * Reads session by calling configured save handler and assigns it to
     * Nishchay session variable.
     * 
     * @param string $sessionId
     * @return string   Always returns empty string to make $_SESSION unusable.
     */
    public function read($sessionId)
    {

        $this->sessionId = $sessionId;
        static::$data = Coding::unserialize(hex2bin($this->handler
                                        ->getHandler()
                                        ->read($sessionId)));
        $this->refactor();

        # If its not an array we will empty session data thinking that
        # persisted session data has been tempered.
        if (!is_array(static::$data)) {
            static::$data = [];
        }

        return '';
    }

    /**
     * Decrements counter of Till next request session.
     * 
     * @return array
     */
    private function refactor()
    {
        # Till next request session was not created!
        if (!isset(static::$data[TillNextRequestCount::NAME])) {
            return;
        }
        $tillNext = static::$data[TillNextRequestCount::NAME];

        # No data exists in till next request session.
        if (empty($tillNext)) {
            return;
        }

        # Iterating over each session data to decrement its counter.
        # We will remove data whose counter is less than zero.
        foreach ($tillNext as $name => $sessionData) {
            $tillNext[$name]['counter'] = --$sessionData['counter'];
            if ($sessionData['counter'] < 0) {
                unset($tillNext[$name]);
            }
        }
        static::$data[TillNextRequestCount::NAME] = $tillNext;
    }

    /**
     * Writes session data to location where it was configured by calling
     * configured handler.
     * 
     * @param string $sessionId    Session ID.
     * @param array $data   Session Data.
     * @return boolean  TRUE on success otherwise FALSE.
     */
    public function write($sessionId, $data)
    {
        if ($this->sessionId !== $sessionId) {
            $this->destroy($this->sessionId, false);
            $this->sessionId = $sessionId;
        }

        return $this->handler
                        ->getHandler()
                        ->write($sessionId, Coding::serialize(static::$data, true));
    }

    /**
     * Removes persisted session data of given session ID.
     * 
     * @param string $sessionId    Session ID.
     * @param string $empty If TRUE then this method makes Nishchay session data
     *                      empty else it leaves session data as it is.
     * @return boolean  TRUE on success otherwise FALSE
     */
    public function destroy($sessionId, $empty = true)
    {
        if ($empty) {
            # We don't remove internal session data even if session destroy is
            # called.
            $internal = (new InternalSession)
                    ->getAll();
            static::$data = [];
            static::$data[InternalSession::NAME] = $internal;
        }
        return $this->handler
                        ->getHandler()
                        ->destroy($sessionId);
    }

    /**
     * 
     * @param type $maxlifetime
     * @return type
     */
    public function gc($maxlifetime)
    {
        return $this->handler
                        ->getHandler()
                        ->gc($maxlifetime);
    }

}
