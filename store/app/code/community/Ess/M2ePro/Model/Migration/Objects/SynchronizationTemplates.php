<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_SynchronizationTemplates extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = '';
    const TABLE_NAME_NEW = 'm2epro_synchronizations_templates';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_SynchronizationTemplates');
    }

    // ########################################

	public function process()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameNew,'*')
                                              ->where("`title` = 'Default'");

        $row = $this->mySqlReadConnection->fetchRow($dbSelect);

        if ($row !== false) {
            $this->tempDbTable->addValue('synchronization_templates.id',0,(int)$row['id']);
            return;
        }

        $newItem = array(
            'title' => 'Default',
            
            'start_auto_list' => Ess_M2ePro_Model_SynchronizationsTemplates::START_AUTO_LIST_NONE,
            'end_auto_stop' => Ess_M2ePro_Model_SynchronizationsTemplates::END_AUTO_STOP_NONE,

            'revise_update_ebay_qty' => Ess_M2ePro_Model_SynchronizationsTemplates::REVISE_UPDATE_EBAY_QTY_YES,
            'revise_update_ebay_price' => Ess_M2ePro_Model_SynchronizationsTemplates::REVISE_UPDATE_EBAY_PRICE_YES,
            'revise_update_title' => Ess_M2ePro_Model_SynchronizationsTemplates::REVISE_UPDATE_TITLE_YES,
            'revise_update_sub_title' => Ess_M2ePro_Model_SynchronizationsTemplates::REVISE_UPDATE_SUB_TITLE_YES,
            'revise_update_description' => Ess_M2ePro_Model_SynchronizationsTemplates::REVISE_UPDATE_DESCRIPTION_YES,
            'revise_change_selling_format_template' => Ess_M2ePro_Model_SynchronizationsTemplates::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_YES,
            'revise_change_description_template' => Ess_M2ePro_Model_SynchronizationsTemplates::REVISE_CHANGE_DESCRIPTION_TEMPLATE_YES,
            'revise_change_listing_template' => Ess_M2ePro_Model_SynchronizationsTemplates::REVISE_CHANGE_LISTING_TEMPLATE_YES,

            'relist_mode' => Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_MODE_NONE,
            'relist_filter_user_lock' => Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_FILTER_USER_LOCK_YES,
            'relist_status_enabled' => Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_STATUS_ENABLED_YES,
            'relist_is_in_stock' => Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_IS_IN_STOCK_YES,
            'relist_qty' => Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_QTY_NONE,
            'relist_qty_value' => '',
            'relist_qty_value_max' => '',
            'relist_schedule_type' => Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_SCHEDULE_TYPE_IMMEDIATELY,
            'relist_schedule_through_value' => 0,
            'relist_schedule_through_metric' => Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_SCHEDULE_THROUGH_METRIC_DAYS,
            'relist_schedule_week' => '',
            'relist_schedule_week_start_time' => NULL,
            'relist_schedule_week_end_time' => NULL,

            'stop_status_disabled' => Ess_M2ePro_Model_SynchronizationsTemplates::STOP_STATUS_DISABLED_YES,
            'stop_out_off_stock' => Ess_M2ePro_Model_SynchronizationsTemplates::STOP_OUT_OFF_STOCK_YES,
            'stop_qty' => Ess_M2ePro_Model_SynchronizationsTemplates::STOP_QTY_NONE,
            'stop_qty_value' => '',
            'stop_qty_value_max' => ''
        );

        $newItem['create_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        $newItem['update_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();

        $this->mySqlWriteConnection->insert($this->tableNameNew,$newItem);
        $newItemId = $this->mySqlWriteConnection->lastInsertId($this->tableNameNew,'id');
        $this->tempDbTable->addValue('synchronization_templates.id',0,(int)$newItemId);
    }

    // ########################################
}