<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_LogsController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/logs')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Activity Logs'));

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/logs');
    }

    //#############################################

    public function indexAction()
	{
        $this->_redirect('*/*/listings');
    }

    //#############################################
    
	public function listingsAction()
	{
        if (!Mage::getSingleton('admin/session')->isAllowed('ebay/logs/listings')) {
            $this->_forward('denied');
            return;
        }
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listings')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        Mage::register('M2ePro_data', $model->getData());

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('Listings Log'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_logs_listings'))
             ->renderLayout();
	}

    public function gridListingsAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listings')->load($id);

        if (!$model->getId() && $id) {
            exit();
        }

        Mage::register('M2ePro_data', $model->getData());

        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_logs_listings_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function ebayListingsAction()
	{
        if (!Mage::getSingleton('admin/session')->isAllowed('ebay/logs/ebay_listings')) {
            $this->_forward('denied');
            return;
        }
        
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/EbayListings')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('3rd Party Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        Mage::register('M2ePro_data', $model->getData());

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('3rd Party Listings Log'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_logs_ebayListings'))
             ->renderLayout();
	}

    public function gridEbayListingsAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/EbayListings')->load($id);

        if (!$model->getId() && $id) {
            exit();
        }

        Mage::register('M2ePro_data', $model->getData());

        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_logs_ebayListings_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function synchronizationsAction()
	{
        if (!Mage::getSingleton('admin/session')->isAllowed('ebay/logs/synchronizations')) {
            $this->_forward('denied');
            return;
        }

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('Synchronization Log'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_logs_synchronizations'))
             ->renderLayout();
	}

    public function gridSynchronizationsAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_logs_synchronizations_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################
}