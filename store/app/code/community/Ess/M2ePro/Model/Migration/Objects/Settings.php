<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_Settings extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e_shedule_task_settings';
    const TABLE_NAME_NEW = '';

    protected $_tableNameOldFeedbackSettings;
    protected $_tableNameOldLastupdateTime;
    protected $_tableNameOldLogCleaningSettings;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_Settings');

        $this->_tableNameOldFeedbackSettings = Mage::getSingleton('core/resource')->getTableName('m2e_feedbacks_settings');
        $this->_tableNameOldLastupdateTime = Mage::getSingleton('core/resource')->getTableName('m2e_store_management');
        $this->_tableNameOldLogCleaningSettings = Mage::getSingleton('core/resource')->getTableName('m2e_logcleaning_settings');
    }

    // ########################################

    public function process()
    {
        $this->_migrateOrderImportSettings();
        $this->_migrateFeedbackImportSettings();
        $this->_migrateLastUpdateTime();
        $this->_migrateLogCleaningSettings();
        $this->_disableOldSynchronization();
    }

    protected function _disableOldSynchronization()
    {
        $this->mySqlReadConnection->update($this->tableNameOld,array('value'=>0));
    }

    protected function _migrateLogCleaningSettings()
    {
        // On m2e 2.x we have only one settings for synchronization log. Duplicate this settings to all log cleaning
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->_tableNameOldLogCleaningSettings,'*')
                                              ->where('`id` = 2');

        $logCleanSettings = $this->mySqlReadConnection->fetchRow($dbSelect);
        if ($logCleanSettings['value'] == 1) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/logs/cleaning/ebay_listings/', 'mode', 1);
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/logs/cleaning/ebay_listings/', 'days', $logCleanSettings['days']);
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/logs/cleaning/listings/', 'mode', 1);
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/logs/cleaning/listings/', 'days', $logCleanSettings['days']);
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/logs/cleaning/synchronizations/', 'mode', 1);
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/logs/cleaning/synchronizations/', 'days', $logCleanSettings['days']);
        } else {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/logs/cleaning/ebay_listings/', 'mode', 0);
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/logs/cleaning/listings/', 'mode', 0);
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/logs/cleaning/synchronizations/', 'mode', 0);
        }
    }

    protected function _migrateLastUpdateTime()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->_tableNameOldLastupdateTime,'*');

        $updateTimeLast = $this->mySqlReadConnection->fetchAll($dbSelect);
        $stockLevelLastRun = isset($updateTimeLast[0]['check_time']) ? $updateTimeLast[0]['check_time'] : false;
        $orderLastRun = isset($updateTimeLast[1]['check_time']) ? $updateTimeLast[1]['check_time'] : false;

        if ($stockLevelLastRun) {
            $stockLevelLastRun = Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($stockLevelLastRun);
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/defaults/update_listings_products/', 'since_time', $stockLevelLastRun);
        }

        if ($orderLastRun) {
            $orderLastRun = Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($orderLastRun);
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/orders/', 'since_time', $orderLastRun);
        }
    }

    protected function _migrateFeedbackImportSettings()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameOld,'value')
                                              ->where('`id` = 9');

        $feedbackSettingsValue = $this->mySqlReadConnection->fetchOne($dbSelect);
        $feedbackSettings = array(
            'feedbacks_receive' => ($feedbackSettingsValue == 1) ? Ess_M2ePro_Model_Accounts::FEEDBACKS_RECEIVE_YES : Ess_M2ePro_Model_Accounts::FEEDBACKS_RECEIVE_NO,
            'feedbacks_auto_response' => Ess_M2ePro_Model_Accounts::FEEDBACKS_AUTO_RESPONSE_NONE,
            'feedbacks_auto_response_only_positive' => Ess_M2ePro_Model_Accounts::FEEDBACKS_AUTO_RESPONSE_ONLY_POSITIVE_YES
        );

        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->_tableNameOldFeedbackSettings,'*');

        $oldFeedbackSettingsValues = $this->mySqlReadConnection->fetchRow($dbSelect);
        $feedbackSettings['feedbacks_auto_response'] = isset($oldFeedbackSettingsValues['mode']) ? $oldFeedbackSettingsValues['mode'] : 0; // 0 - none

        // Apply feedback import settings to each account
        foreach (Mage::getModel('M2ePro/Accounts')->getCollection() as $loadedAccountModel) {
            $loadedAccountModel->addData($feedbackSettings)->save();
        }

        if ($feedbackSettings['feedbacks_receive'] != Ess_M2ePro_Model_Accounts::FEEDBACKS_RECEIVE_NO) {
            // Import feedback enabled, enable related synchronization task
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/feedbacks/', 'mode', 1);
        }
    }

    protected function _migrateOrderImportSettings()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameOld,'value')
                                              ->where('`id` = 6');

        $orderSettingsValue = $this->mySqlReadConnection->fetchOne($dbSelect);
        
        /**
         * Type of synchronization
         *
         * 0 - Only by Listing (default)
         * 1 - Only by Product SKU
         * 2 - Listing & Product SKU
         */
        $synchronizationBy = 0;

        // 0 - no notify,  1 - notify user, 2 - user & order create, 3 - on order only
        $notificationMode = 0; // No notification
        /**
         * 0 - Guest checkout,
         * 1 - register each user,
         * 2 - assign each order to single customer
         */
        $checkoutMode = 0;
        $workMode = $orderSettingsValue;

        if ($workMode > 20) {
            $workMode = $workMode - 20;
            $synchronizationBy = 2;
        } else if ($workMode > 10) {
            $workMode = $workMode - 10;
            $synchronizationBy = 1;
        }
        $accountSettingDefault = array(
            'orders_mode' => Ess_M2ePro_Model_Accounts::ORDERS_MODE_NO,
            'orders_listings_mode' => Ess_M2ePro_Model_Accounts::ORDERS_LISTINGS_MODE_NO,
            'orders_listings_store_mode' => Ess_M2ePro_Model_Accounts::ORDERS_LISTINGS_STORE_MODE_NO,

            'orders_ebay_mode' => Ess_M2ePro_Model_Accounts::ORDERS_EBAY_MODE_NO,
            'orders_ebay_store_id' => '',
            'orders_ebay_create_product' => Ess_M2ePro_Model_Accounts::ORDERS_EBAY_CREATE_PRODUCT_NO,
            'orders_customer_mode' => Ess_M2ePro_Model_Accounts::ORDERS_CUSTOMER_MODE_GUEST,

            'orders_status_checkout_incomplete' => Ess_M2ePro_Model_Accounts::ORDERS_CHECKOUT_MODE_COMPLETED

        );
        // $workMode - 1 - only transaction  2 - guest, 3 - regis, 4 - predef

        if ($workMode >= 1) {
            $accountSettingDefault['orders_mode'] = Ess_M2ePro_Model_Accounts::ORDERS_MODE_YES;
        }

        if ($workMode > 1) {
            if ($synchronizationBy == 0 || $synchronizationBy == 2) {
                // by listings or listing + sku
                $accountSettingDefault['orders_listings_mode'] = Ess_M2ePro_Model_Accounts::ORDERS_LISTINGS_MODE_YES;
                $accountSettingDefault['orders_listings_store_mode'] = Ess_M2ePro_Model_Accounts::ORDERS_LISTINGS_STORE_MODE_NO; // from listing
            }

            if ($synchronizationBy == 1 || $synchronizationBy == 2) {
                $accountSettingDefault['orders_ebay_mode'] = Ess_M2ePro_Model_Accounts::ORDERS_EBAY_MODE_YES;
            }
        }

        if ($workMode > 4) {
            $checkoutMode = 1; // Register each customer
            $notificationMode = $workMode - 4; // 1 - notify user, 2 - user & order create, 3 - on order only
        } else if ($workMode > 1) {
            // 3 - predefined
            // 2 - register each
            $checkoutMode = $workMode - 2;
            $notificationMode = 0;
        }
        if ($checkoutMode == 2) {
            // Register each
            $accountSettingDefault['orders_customer_mode'] = Ess_M2ePro_Model_Accounts::ORDERS_CUSTOMER_MODE_NEW;
            $accountSettingDefault['orders_customer_new_subscribe_news'] = Ess_M2ePro_Model_Accounts::ORDERS_CUSTOMER_NEW_SUBSCRIBE_NEWS_NO;
            $accountSettingDefault['orders_customer_new_send_notifications'] = "a0_o0_i0"; // a1_o1_i1
            if ($notificationMode == 1) {
                $accountSettingDefault['orders_customer_new_send_notifications'] = "a1_o0_i0";
            } else if ($notificationMode == 2) {
                $accountSettingDefault['orders_customer_new_send_notifications'] = "a1_o1_i1";
            } else if ($notificationMode == 3) {
                $accountSettingDefault['orders_customer_new_send_notifications'] = "a0_o1_i1";
            }

            $accountSettingDefault['orders_customer_new_group'] = 1; // Default customer group
            $accountSettingDefault['orders_customer_new_website'] = Mage::helper('M2ePro/Sales')->getDefaultWebsiteId();
        } else if ($checkoutMode == 3) {
            // predefined customer
            $accountSettingDefault['orders_customer_mode'] = Ess_M2ePro_Model_Accounts::ORDERS_CUSTOMER_MODE_EXIST;
            $accountSettingDefault['orders_customer_exist_id'] = 0;
        }

        // Apply order import settings to each account  
        foreach (Mage::getModel('M2ePro/Accounts')->getCollection() as $loadedAccountModel) {
            $loadedAccountModel->addData($accountSettingDefault)->save();
        }

        if ($accountSettingDefault['orders_mode'] != Ess_M2ePro_Model_Accounts::ORDERS_MODE_NO) {
            // Import order enabled, enable related synchronization task
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/orders/', 'mode', 1);
        }
    }

    // ########################################
}