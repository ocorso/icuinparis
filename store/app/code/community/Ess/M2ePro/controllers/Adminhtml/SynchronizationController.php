<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_SynchronizationController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/configuration')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Synchronization'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Controls/ProgressBar.js')
             ->addCss('M2ePro/css/Controls/ProgressBar.css')
             ->addJs('M2ePro/Controls/AreaWrapper.js')
             ->addCss('M2ePro/css/Controls/AreaWrapper.css')
             ->addJs('M2ePro/SynchProgressHandlers.js')
             ->addJs('M2ePro/Configuration/SynchronizationHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/configuration/synchronization');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_synchronization'))
             ->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/templates/', 'mode', (int)$this->getRequest()->getParam('templates_mode'));

        $inspectorInterval = (int)$this->getRequest()->getParam('inspector_interval');
        $inspectorInterval <= 0 && $inspectorInterval = 60;

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/templates/inspector/', 'mode', (int)$this->getRequest()->getParam('inspector_mode'));
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/templates/inspector/', 'interval', $inspectorInterval*60);
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/orders/', 'mode', (int)$this->getRequest()->getParam('orders_mode'));
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/feedbacks/', 'mode', (int)$this->getRequest()->getParam('feedbacks_mode'));
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/ebay_listings/', 'mode', (int)$this->getRequest()->getParam('ebay_listings_mode'));
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/messages/', 'mode', (int)$this->getRequest()->getParam('messages_mode'));

        exit();
    }

    public function clearLogAction()
    {
        $synchTask = $this->getRequest()->getParam('synch_task');

        if (is_null($synchTask)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to clear'));
            return $this->_redirect('*/*/index');
        }

        Mage::getModel('M2ePro/Synchronization_Logs')->clearMessages($synchTask);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The synchronization task log has been successfully cleaned.'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('index'));
    }

    //#############################################

    public function runAllEnabledNowAction()
    {
        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->process(array(
            Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES,
            Ess_M2ePro_Model_Synchronization_Tasks::ORDERS,
            Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS,
            Ess_M2ePro_Model_Synchronization_Tasks::EBAY_LISTINGS,
            Ess_M2ePro_Model_Synchronization_Tasks::MESSAGES
        ), Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_USER, array());
    }

    //------------------------

    public function runNowTemplatesAction()
    {
        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->process(array(
            Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES
        ), Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_USER, array());
    }

    public function runNowOrdersAction()
    {
        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->process(array(
            Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Tasks::ORDERS
        ), Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_USER, array());
    }

    public function runNowFeedbacksAction()
    {
        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->process(array(
            Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS
        ), Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_USER, array());
    }

    public function runNowEbayListingsAction()
    {
        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->process(array(
            Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Tasks::EBAY_LISTINGS
        ), Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_USER, array());
    }

    public function runNowMessagesAction()
    {
        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->process(array(
            Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Tasks::MESSAGES
        ), Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_USER, array());
    }

    //#############################################
}