<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_SettingsController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/configuration')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Settings'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Configuration/SettingsHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/configuration/settings');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_settings'))
             ->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/products/settings/', 'show_thumbnails', (int)$this->getRequest()->getParam('products_show_thumbnails'));
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/block_notices/settings/', 'show', (int)$this->getRequest()->getParam('block_notices_show'));
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/feedbacks/notification/', 'mode', (int)$this->getRequest()->getParam('feedbacks_notification_mode'));
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/messages/notification/', 'mode', (int)$this->getRequest()->getParam('messages_notification_mode'));
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/cron/notification/', 'mode', (int)$this->getRequest()->getParam('cron_notification_mode'));

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The global settings have been successfully saved.'));
        $this->_redirect('*/*/index');
    }

    //#############################################

    public function restoreBlockNoticesAction()
    {
        foreach ($_COOKIE as $name => $value) {
            setcookie($name, '', 0, '/');
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('All help blocks were restored.'));
        $this->_redirect('*/*/index');
    }

    //#############################################

    public function startCustomUserInterfaceAction()
    {
        Mage::getModel('M2ePro/Migration_Dispatcher')->startCustomUserInterface();
    }

    public function endCustomUserInterfaceAction()
    {
        Mage::getModel('M2ePro/Migration_Dispatcher')->endCustomUserInterface();
    }

    //---------------------------

    public function startMigrationAction()
    {
        Mage::getModel('M2ePro/Migration_Dispatcher')->process();
    }

    //#############################################
}