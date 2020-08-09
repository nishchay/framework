<?php

namespace Nishchay\Data\Meta\Detail;

/**
 *
 * @author Bhavik Patel
 */
interface MetaDetailInterface {

    /**
     * 
     */
    public function getTables();

    /**
     * 
     * @param type $table
     */
    public function isTableExist($table);

    /**
     * 
     * @param type $table
     * @param type $foreign
     */
    public function getTableColumns($table, $foreign = TRUE);

    /**
     * 
     * @param type $table
     * @param type $name
     * @param type $foreign
     */
    public function getTableColumn($table, $name, $foreign = TRUE);

    /**
     * 
     * @param type $table
     */
    public function getPrimaryKey($table);

    /**
     * 
     * @param type $table
     * @param type $column
     */
    public function getForeignKeys($table, $column = NULL);

    /**
     * 
     * @param type $table
     */
    public function getChildTables($table);

    /**
     * Returns indexes defined on table or table column.
     * 
     * @param type $table
     * @param type $column
     * @return array
     */
    public function getIndexes($table, $column = null);
}
