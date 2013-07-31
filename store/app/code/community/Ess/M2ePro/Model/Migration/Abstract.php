<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Migration_Abstract extends Mage_Core_Model_Abstract
{
    protected $tableNameOld = '';
    protected $tableNameNew = '';
    
    /**
     * @var Ess_M2ePro_Model_Migration_TempDbTable
     */
    protected $tempDbTable = NULL;

    /**
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $mySqlReadConnection = NULL;

    /**
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $mySqlWriteConnection = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();

        $this->tempDbTable  = Mage::getModel('M2ePro/Migration_TempDbTable');
        
        $this->mySqlReadConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->mySqlWriteConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $reflectionClass = new ReflectionClass($this);
        $childClass = $reflectionClass->getName();

        $tableNameOld = $tableNameNew = '';
        eval('$tableNameOld = '.$childClass.'::TABLE_NAME_OLD;');
        eval('$tableNameNew = '.$childClass.'::TABLE_NAME_NEW;');

        $this->tableNameOld  = Mage::getSingleton('core/resource')->getTableName($tableNameOld);
        $this->tableNameNew  = Mage::getSingleton('core/resource')->getTableName($tableNameNew);
    }

    // ########################################
    
    abstract public function process();

    // ########################################

    protected function getLikeExistItem($newItem, $ignoreTitle = true, $attributeSet = NULL)
    {
        $whereSql = '';
        foreach ($newItem as $key => $value) {
            if ($ignoreTitle && $key == 'title') {
                continue;
            }
            if ($whereSql == '') {
                $whereSql .= ' ';
            } else {
                $whereSql .= ' AND ';
            }
            $whereSql .= ' `'.$key.'` ';
            is_null($value) && $whereSql .= ' IS NULL ';
            is_string($value) && $whereSql .= ' = '.$this->mySqlReadConnection->quote($value).' ';
            (is_integer($value) || is_float($value)) && $whereSql .= ' = '.$value.' ';
        }

        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameNew,'*');
        $whereSql != '' && $dbSelect->where($whereSql);

        $row = $this->mySqlReadConnection->fetchRow($dbSelect);

        if ($row === false) {
            return NULL;
        }

        if (!is_null($attributeSet)) {

            $templateId = (int)$row['id'];
            $templateType = (int)$attributeSet['template_type'];
            $attributeSetId = (int)$attributeSet['attribute_set_id'];

            $tasTable = Mage::getResourceModel('M2ePro/TemplatesAttributeSets')->getMainTable();

            $dbSelect = $this->mySqlReadConnection->select()
                                                  ->from($tasTable,'*')
                                                  ->where('template_type = ?',$templateType)
                                                  ->where('template_id = ?',$templateId);
            $rowsAttributesSets = $this->mySqlReadConnection->fetchAll($dbSelect);

            if ($rowsAttributesSets === false || count($rowsAttributesSets) != 1) {
                return NULL;
            }

            $rowAttributeSet = $rowsAttributesSets[0];

            if ((int)$rowAttributeSet['attribute_set_id'] != $attributeSetId) {
                return NULL;
            }
        }

        return $row;
    }

    // ########################################
}