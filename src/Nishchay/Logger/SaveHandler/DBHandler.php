<?php

namespace Nishchay\Logger\SaveHandler;

use Nishchay;
use DateTime;
use Nishchay\Data\Query;

/**
 * DB save Handler for Logger.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DBHandler extends AbstractSaveHandler
{

    /**
     * Request Log Table name.
     * 
     * @var string 
     */
    private $requestLogTableName;

    /**
     *
     * @var \Nishchay\Data\Query 
     */
    private $query;

    public function __construct()
    {
        $db = Nishchay::getSetting('logger.db');
        $this->requestLogTableName = $db->table;
        $this->query = new Query($db->connection);
    }

    /**
     * Simply returns true.
     * 
     * @return boolean
     */
    public function close()
    {
        # Not needed
        return true;
    }

    /**
     * Simply returns true.
     * 
     * @return boolean
     */
    public function open()
    {
        # Not needed
        return true;
    }

    /**
     * Returns true if log written to DB table.
     * 
     * @return boolean
     */
    public function write(string $type, string $logLine)
    {
        $saved = $this->query
                ->setTable($this->requestLogTableName)
                ->setColumnWithValue([
                    'type' => $type,
                    'message' => $logLine,
                    'createdAt' => (new DateTime)
                ])
                ->insert();
        return $saved ? true : false;
    }

}
