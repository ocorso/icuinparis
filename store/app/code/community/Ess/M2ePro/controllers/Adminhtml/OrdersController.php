<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_OrdersController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/sales')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Sales'))
             ->_title(Mage::helper('M2ePro')->__('eBay Orders'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/OrdersHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/sales/ebay_orders');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_orders'));

        $this->renderLayout();
    }

    //#############################################

    public function gridOrdersAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_orders_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    public function gridOrderItemsAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Orders_Order')->load($id);

        if (!$model->getId() && $id) {
            return;
        }

        Mage::register('M2ePro_data', $model);

        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_orders_view_items')->toHtml();
        $this->getResponse()->setBody($response);
    }

    public function gridOrderLogsAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Orders_Order')->load($id);

        if (!$model->getId() && $id) {
            return;
        }

        Mage::register('M2ePro_data', $model);

        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_orders_view_logs')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Orders_Order')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Order does not exist.'));
            return $this->_redirect('*/*/index');
        }

        Mage::register('M2ePro_data', $model);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_orders_view_order'))
             ->renderLayout();
    }

    //#############################################

    private function processConnector($action, array $params = array())
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select order(s).'));
            return $this->_redirect('*/*/index');
        }

        $ordersIds = array();
        !is_null($id) && $ordersIds[] = $id;
        !is_null($ids) && $ordersIds = array_merge($ordersIds,(array)$ids);

        return Mage::getModel('M2ePro/Connectors_Ebay_Order_Dispatcher')->process($action, $ordersIds, $params);
    }

    //--------------------

    public function payOrderOnEbayAction()
    {
        if ($this->processConnector(Ess_M2ePro_Model_Connectors_Ebay_Order_Dispatcher::ACTION_PAY)) {
            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Payment status for selected eBay Order(s) was updated to Paid.'));
        } else {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Payment status for selected eBay Order(s) was not updated.'));
        }

        $id = $this->getRequest()->getParam('id');

        return is_null($id) ? $this->_redirect('*/*/index') : $this->_redirect('*/*/view', array('id' => $id));
    }

    public function shipOrderOnEbayAction()
    {
        if ($this->processConnector(Ess_M2ePro_Model_Connectors_Ebay_Order_Dispatcher::ACTION_SHIP)) {
            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Shipping status for selected eBay Order(s) was updated to Shipped.'));
        } else {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Shipping status for selected eBay Order(s) was not updated.'));
        }

        $id = $this->getRequest()->getParam('id');

        return is_null($id) ? $this->_redirect('*/*/index') : $this->_redirect('*/*/view', array('id' => $id));
    }

    //#############################################

    public function createOrderAction()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var $order Ess_M2ePro_Model_Orders_Order */
        $order = Mage::getModel('M2ePro/Orders_Order')->load($id);
        
        if (!$order->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Order does not exist.'));
            return $this->_redirect('*/*/index');
        }

        if ($order->getData('magento_order_id') > 0) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Magento Order is already created for this eBay Order.'));
            return $this->_redirect('*/*/view/', array('id' => $id));
        }

        // Create magento order
        // -------------
        $result = $order->createMagentoOrder();

        $result && $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Magento Order was created.'));
        !$result && $this->_getSession()->addError(Mage::helper('M2ePro')->__('Magento Order was not created.'));
        // -------------

        // Create payment transaction
        // -------------
        $order->createPaymentTransactionForMagentoOrder();
        // -------------

        // Create invoice
        // -------------
        $result = $order->createInvoiceForMagentoOrder();
        $result && $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Invoice was created.'));
        // -------------

        // Create shipment
        // -------------
        $result = $order->createShipmentForMagentoOrder();
        $result && $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Shipment was created.'));
        // -------------

        $this->_redirect('*/*/view', array('id' => $id));
    }

    //#############################################

    public function goToPaypalTransactionPageAction()
    {
        $transactionId = $this->getRequest()->getParam('transaction_id');

        if (!$transactionId) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Transaction ID should be defined.'));
            return $this->_redirect('*/*/index');
        }

        $collection = Mage::getModel('M2ePro/Orders_Order')->getCollection();
        $collection->getSelect()
                   ->join(array('meoet'=>Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_orders_external_transactions')),
                          '(`meoet`.`order_id` = `main_table`.`id`)',
                          array('transaction_id' => 'ebay_id')
                   )
                   ->joinLeft(array('ma'=>Mage::getSingleton('core/resource')->getTableName('m2epro_accounts')),
                          '(`ma`.`id` = `main_table`.`account_id`)',
                          array('account_mode' => 'mode')
                   )
                   ->where('`meoet`.`ebay_id` = ?',$transactionId);

        $order = $collection->getFirstItem();

        if (!$order->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Order does not exist.'));
            return $this->_redirect('*/*/index');
        }

        $params = array(
            'cmd' => '_view-a-trans',
            'id'  => $transactionId
        );

        $modePrefix = '';
        $order->getData('account_mode') == Ess_M2ePro_Model_Accounts::MODE_SANDBOX && $modePrefix = 'sandbox.';

        $baseUrl = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/other/paypal/', 'url');
        $url = 'https://www.' . $modePrefix . $baseUrl . '?' . http_build_query($params, '', '&');

        return $this->_redirectUrl($url);
    }

    //#############################################
}