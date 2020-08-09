<?php

namespace Nishchay\Session\SaveHandler;

use Nishchay;
use Nishchay\Data\Query;
use Nishchay\Utility\DateUtility;

/**
 * Session save handler type DB.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DBHandler extends AbstractSaveHandler
{

    /**
     * Instance of query.
     * 
     * @var Query 
     */
    private $query;

    /**
     * Session data.
     * 
     * @var array 
     */
    private $data;

    /**
     * Session table name where session data need to be stored.
     * 
     * @var type 
     */
    private $table;

    /**
     * Session Id.
     * 
     * @var type 
     */
    private $sessionId;

    /**
     * Prepares Database query and sets table name.
     * 
     * @param string $save_path
     * @param string $session_name
     * @return boolean
     */
    public function open($save_path, $session_name)
    {
        $config = Nishchay::getConfig('session.db');
        $this->table = isset($config->table) ? $config->table : 'Session';
        $this->query = new Query(isset($config->connection) ? $config->connection : null);
        return true;
    }

    /**
     * Returns session data from table.
     * 
     * @param type $session_id
     * @return type
     */
    private function getData($session_id)
    {
        $data = $this->query->setTable($this->table)
                ->setColumn('data')
                ->setCondition(['sessionId' => $session_id])
                ->getOne();

        return $this->data = isset($data->data) ? $data->data : '';
    }

    /**
     * Reads session data from table.
     * 
     * @param type $session_id
     * @return type
     */
    public function read($session_id)
    {
        $this->sessionId = $session_id;
        return $this->getData($session_id);
    }

    /**
     * Writes session data to table.
     * 
     * @param string $session_id
     * @param string $data
     * @return boolean
     */
    public function write($session_id, $data)
    {

        $this->query->setTable($this->table)
                ->setColumnWithValue([
                    'data' => $data,
                    'accessAt' => DateUtility::formatNow('U')
        ]);

        if (empty($this->sessionId !== $session_id ? '' : $this->data)) {
            $this->sessionId = $session_id;
            $this->query->setColumnWithValue([
                'sessionId' => $session_id
            ])->insert();
            return true;
        }

        $this->query->setCondition('sessionId', $session_id)
                ->update();
        return true;
    }

    /**
     * Just returns TRUE.
     * 
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Removes session data from table.
     * 
     * @param type $session_id
     * @return boolean
     */
    public function destroy($session_id)
    {
        $this->query->setTable($this->table)
                ->setCondition('sessionId', $session_id)
                ->remove();
        return true;
    }

    /**
     * Removes expired session data from table.
     * 
     * @param int $maxlifetime
     */
    public function gc($maxlifetime)
    {
        $this->query->setTable($this->table)
                ->setCondition(['accessAt<' => time() - $maxlifetime]);
        return true;
    }

}
