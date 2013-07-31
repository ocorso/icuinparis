<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_TempDbTable extends Mage_Core_Model_Abstract
{
    const TABLE_NAME = 'm2epro_migration_temp';
    
    private $tableName = '';

    /**
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    private $mySqlReadConnection = NULL;

    /**
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    private $mySqlWriteConnection = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_TempDbTable');

        $this->tableName  = Mage::getSingleton('core/resource')->getTableName(self::TABLE_NAME);
        $this->mySqlReadConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->mySqlWriteConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    // ########################################

    public function clear()
    {
        $this->mySqlWriteConnection->delete($this->tableName,'id > 0');
    }

    // ########################################

    public function addValue($type, $oldValue, $newValue)
    {
        $data = array(
           'type' => $type,
           'oldValue' => $oldValue,
           'newValue' => $newValue
        );
        $this->mySqlWriteConnection->insert($this->tableName,$data);
    }

    public function getOldValue($type, $newValue)
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableName,'oldValue')
                                              ->where('`type` = ?',(string)$type)
                                              ->where('`newValue` = ?',(string)$newValue);

        $result = $this->mySqlReadConnection->fetchCol($dbSelect);
        return $result ? $result[0] : false;
    }

    public function getNewValue($type, $oldValue)
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableName,'newValue')
                                              ->where('`type` = ?',(string)$type)
                                              ->where('`oldValue` = ?',(string)$oldValue);

        $result = $this->mySqlReadConnection->fetchCol($dbSelect);
        return $result ? $result[0] : false;
    }

    // ########################################

    public function deleteValues($type)
    {
        $this->mySqlWriteConnection->delete($this->tableName,"type = '{$type}'");
    }

    public function getValues($type)
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableName,'*')
                                              ->where('`type` = ?',(string)$type);

        return $this->mySqlReadConnection->fetchAll($dbSelect);
    }

    // ########################################
}