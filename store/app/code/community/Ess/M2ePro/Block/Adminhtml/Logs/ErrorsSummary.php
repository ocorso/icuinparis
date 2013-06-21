<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Logs_ErrorsSummary extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('errorsSummary');
        //------------------------------

        $this->setTemplate('M2ePro/logs/errors_summary.phtml');
    }

    protected function _beforeToHtml()
    {
        $tableName = $this->getData('table_name');
        $actionIdsString = $this->getData('action_ids');

        $countField = 'product_id';
        if ($this->getData('type_log') == 'ebay_listings') {
            $countField = 'ebay_listing_id';
        }

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $dbSelect = $connRead->select()
                             ->from($tableName,new Zend_Db_Expr('COUNT(`'.$countField.'`) as `count_products`, `description`'))
                             ->where('`action_id` IN ('.$actionIdsString.')')
                             ->where('`type` = ?',Ess_M2ePro_Model_LogsBase::TYPE_ERROR)
                             ->group('description')
                             ->order(array('count_products DESC'))
                             ->limit(100);

        $newErrors = array();
        $tempErrors = $connRead->fetchAll($dbSelect);
        
        foreach ($tempErrors as $row) {
            $row['description'] = Mage::helper('M2ePro')->escapeHtml($row['description']);
            $row['description'] = Mage::getModel('M2ePro/LogsBase')->decodeDescription($row['description']);
            $newErrors[] = $row;
        }

        $this->errors = $newErrors;

        return parent::_beforeToHtml();
    }
}