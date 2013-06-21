<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_EbayListingsController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/manage_listings')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
             ->_title(Mage::helper('M2ePro')->__('3rd Party Listings'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Controls/ProgressBar.js')
             ->addCss('M2ePro/css/Controls/ProgressBar.css')
             ->addJs('M2ePro/Controls/AreaWrapper.js')
             ->addCss('M2ePro/css/Controls/AreaWrapper.css')
             ->addJs('M2ePro/Listings/EbayItemsGridHandlers.js')
             ->addJs('M2ePro/Listings/EbayActionsHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/manage_listings/ebay_listings');
    }
    //#############################################

    public function indexAction()
    {
        // Check 3rd listing lock item
        //----------------------------
        $lockItem = Mage::getModel('M2ePro/ListingsLockItem',array('id'=>'listingsEbay'));
        if ($lockItem->isExist()) {
            $this->_getSession()->addWarning(Mage::helper('M2ePro')->__('The 3rd party listings are locked by another process. Please try again later.'));
        }
        //----------------------------

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listings_ebay'))
             ->renderLayout();
    }

    public function gridListingsAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_ebay_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function clearLogAction()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to clear'));
            return $this->_redirect('*/*/index');
        }

        $idsForClear = array();
        !is_null($id) && $idsForClear[] = (int)$id;
        !is_null($ids) && $idsForClear = array_merge($idsForClear,(array)$ids);

        foreach ($idsForClear as $id) {
            Mage::getModel('M2ePro/EbayListingsLogs')->clearMessages($id);
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The 3rd party listing(s) log has been successfully cleaned.'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list'));
    }

    //#############################################

    public function checkLockListingAction()
    {
        $listingId = "listingsEbay";

        $lockItem = Mage::getModel('M2ePro/ListingsLockItem',array('id'=>$listingId));

        if ($lockItem->isExist()) {
            exit('locked');
        }

        exit('unlocked');
    }

    public function lockListingNowAction()
    {
        $listingId = "listingsEbay";

        $lockItem = Mage::getModel('M2ePro/ListingsLockItem',array('id'=>$listingId));

        if (!$lockItem->isExist()) {
            $lockItem->create();
        }

        exit();
    }

    public function unlockListingNowAction()
    {
        $listingId = "listingsEbay";

        $lockItem = Mage::getModel('M2ePro/ListingsLockItem',array('id'=>$listingId));

        if ($lockItem->isExist()) {
            $lockItem->remove();
        }

        exit();
    }

    public function getErrorsSummaryAction()
    {
        $blockParams = array(
            'action_ids' => $this->getRequest()->getParam('action_ids'),
            'table_name' => Mage::getResourceModel('M2ePro/EbayListingsLogs')->getMainTable(),
            'type_log' => 'ebay_listings'
        );
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_logs_errorsSummary','',$blockParams);
        exit($block->toHtml());
    }
    
    //#############################################

    protected function processConnector($action, array $params = array())
    {
        if (!$ebayProductsIds = $this->getRequest()->getParam('selected_products')) {
            exit('You should select products');
        }

        $ebayProductsIds = explode(',', $ebayProductsIds);

        $dispatcherObject = Mage::getModel('M2ePro/Connectors_Ebay_EbayItem_Dispatcher');
        $result = (int)$dispatcherObject->process($action, $ebayProductsIds, $params);
        $actionId = (int)$dispatcherObject->getLogsActionId();

        if ($result == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR) {
            exit(json_encode(array('result'=>'error','action_id'=>$actionId)));
        }

        if ($result == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_WARNING) {
            exit(json_encode(array('result'=>'warning','action_id'=>$actionId)));
        }

        if ($result == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_SUCCESS) {
            exit(json_encode(array('result'=>'success','action_id'=>$actionId)));
        }

        exit(json_encode(array('result'=>'error','action_id'=>$actionId)));
    }

    //--------------------
    
    public function runRelistProductsAction()
    {
        $this->processConnector(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST);
    }

    public function runStopProductsAction()
    {
        $this->processConnector(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP);
    }

    //#############################################
}