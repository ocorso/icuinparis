<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_FeedbacksController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

	protected function _initAction()
	{
        $this->loadLayout()
             ->_setActiveMenu('ebay/communication')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Communication'))
             ->_title(Mage::helper('M2ePro')->__('Feedbacks'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Communication/FeedbacksHandlers.js');

		return $this;
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/communication/feedbacks');
    }
    //#############################################

	public function indexAction()
	{
		$this->_initAction();
		$this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_feedbacks'));
		$this->renderLayout();
	}

    public function gridFeedbacksAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_feedbacks_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function saveAction()
    {
        $feedbackId = $this->getRequest()->getParam('feedback_id');
        $feedbackText = $this->getRequest()->getParam('feedback_text');

        $feedbackText = strip_tags($feedbackText);

        $feedbackModel = Mage::getModel('M2ePro/Feedbacks')->loadInstance($feedbackId);
        $feedbackModel->sendResponse($feedbackText, Ess_M2ePro_Model_Feedbacks::TYPE_POSITIVE);

        $paramsConnector = array(
            'transaction_id' => $feedbackModel->getData('ebay_transaction_id'),
            'item_id'        => $feedbackModel->getData('ebay_item_id')
        );

        Mage::getModel('M2ePro/Feedbacks')->receiveFeedbacks($feedbackModel->getAccount(), $paramsConnector);
    }

    //#############################################

    public function getFeedbacksTemplatesAction()
    {
        $feedbackId = $this->getRequest()->getParam('feedback_id');

        $account = Mage::getModel('M2ePro/Feedbacks')->loadInstance($feedbackId)->getAccount();
        $feedbacksTemplates = $account->getFeedbacksTemplates(false);

        exit(json_encode(array(
            'feedbacks_templates' => $feedbacksTemplates
        )));
    }

    //#############################################

    public function goToOrderAction()
    {
        $feedbackId = $this->getRequest()->getParam('feedback_id');

        if (is_null($feedbackId)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Requested order was not found.'));
            $this->_redirect('*/adminhtml_orders/index');
            return;
        }

        $collection = Mage::getModel('M2ePro/Feedbacks')->getCollection()->addFieldToFilter('main_table.id', (int)$feedbackId);

        $collection->getSelect()
                   ->joinLeft(
                       array('meoi' => Mage::getResourceModel('M2ePro/Orders_OrderItem')->getMainTable()),
                       '(`meoi`.transaction_id = `main_table`.ebay_transaction_id AND `meoi`.item_id = `main_table`.ebay_item_id)',
                       array('order_id'=>'ebay_order_id')
                   )
                   ->limit(1);

        $feedbackItem = $collection->getFirstItem();

        if (is_null($feedbackItem->getId()) || is_null($orderId = $feedbackItem->getData('order_id'))) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Requested order was not found.'));
            $this->_redirect('*/adminhtml_orders/index');
            return;
        }

        $this->_redirect('*/adminhtml_orders/view', array('id' => $orderId));
    }

    //#############################################
}