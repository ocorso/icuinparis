<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_LogsCleaningController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################}

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/configuration')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Logs Clearing'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Configuration/LogsCleaningHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/configuration/logs_cleaning');
    }
    
    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_logsCleaning'))
             ->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        // Save settings
        //--------------------
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            Mage::getModel('M2ePro/LogsCleaning')->saveSettings(Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS,$post['listings_log_mode'],$post['listings_log_days']);
            Mage::getModel('M2ePro/LogsCleaning')->saveSettings(Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS,$post['ebay_listings_log_mode'],$post['ebay_listings_log_days']);
            Mage::getModel('M2ePro/LogsCleaning')->saveSettings(Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS,$post['synchronizations_log_mode'],$post['synchronizations_log_days']);

            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The clearing settings has been successfully saved.'));
        }
        //--------------------

        // Get actions
        //--------------------
        $buttonAction = $this->getRequest()->getParam('button_action');
        $log = $this->getRequest()->getParam('log');

        if (!is_null($buttonAction)) {
            
            switch ($buttonAction) {
                
                case 'run_now':
                    Mage::getModel('M2ePro/LogsCleaning')->clearOldRecords($log);
                    $tempString = Mage::helper('M2ePro')->__('Log for %log% has been successfully cleared.');
                    $this->_getSession()->addSuccess(str_replace('%log%',str_replace('_',' ',$log),$tempString));
                    break;

                case 'clear_all':
                    Mage::getModel('M2ePro/LogsCleaning')->clearAllLog($log);
                    $tempString = Mage::helper('M2ePro')->__('All log for %log% has been successfully cleared.');
                    $this->_getSession()->addSuccess(str_replace('%log%',str_replace('_',' ',$log),$tempString));
                    break;

                case 'run_now_logs':
                    Mage::getModel('M2ePro/LogsCleaning')->clearOldRecords(Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS);
                    Mage::getModel('M2ePro/LogsCleaning')->clearOldRecords(Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS);
                    Mage::getModel('M2ePro/LogsCleaning')->clearOldRecords(Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS);
                    $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('All logs has been successfully cleared.'));
                    break;

                case 'clear_all_logs':
                    Mage::getModel('M2ePro/LogsCleaning')->clearAllLog(Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS);
                    Mage::getModel('M2ePro/LogsCleaning')->clearAllLog(Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS);
                    Mage::getModel('M2ePro/LogsCleaning')->clearAllLog(Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS);
                    $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('All logs has been successfully cleared.'));
                    break;
            }
        }
        //--------------------

        $this->_redirect('*/*/index');
    }
    
    //#############################################
}