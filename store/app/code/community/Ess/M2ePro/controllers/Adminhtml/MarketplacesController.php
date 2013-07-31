<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_MarketplacesController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/configuration')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Marketplaces'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Controls/ProgressBar.js')
             ->addCss('M2ePro/css/Controls/ProgressBar.css')
             ->addJs('M2ePro/Controls/AreaWrapper.js')
             ->addCss('M2ePro/css/Controls/AreaWrapper.css')
             ->addJs('M2ePro/SynchProgressHandlers.js')
             ->addJs('M2ePro/Configuration/MarketplacesHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/configuration/marketplaces');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_marketplaces'))
             ->renderLayout();
    }

    public function saveAction()
    {
        $marketplaces = Mage::getModel('M2ePro/Marketplaces')
                                            ->getCollection()
                                            ->getItems();
        
        foreach ($marketplaces as $marketplace) {
            $newStatus = $this->getRequest()->getParam('status_'.$marketplace->getId());
            if (is_null($newStatus)) {
                 continue;
            }
            if ($marketplace->getStatus() == $newStatus) {
                continue;
            }
            $marketplace->addData(array('status'=>$newStatus))->save();
        }

        exit();
    }

    //#############################################

    public function runSynchNowAction()
    {
        session_write_close();

        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id');

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->process(array(
            Ess_M2ePro_Model_Synchronization_Tasks::MARKETPLACES
        ), Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_USER, array('marketplace_id'=>$marketplaceId));
    }

    //#############################################
}