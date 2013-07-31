<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Ebay_OrderItem extends Mage_Core_Model_Abstract
{
    protected $_data = array();

    protected $_ebayOrder = NULL;

    protected $_orderItem = NULL;

    protected $_account = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Orders_Ebay_OrderItem');
    }

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Orders_Ebay_Order $order
     * @return Ess_M2ePro_Model_Orders_Ebay_OrderItem
     */
    public function setEbayOrder(Ess_M2ePro_Model_Orders_Ebay_Order $order)
    {
        $this->_ebayOrder = $order;

        return $this;
    }

    /**
     * @throws Exception
     * @return Ess_M2ePro_Model_Orders_Ebay_Order
     */
    public function getEbayOrder()
    {
        if (is_null($this->_ebayOrder)) {
            throw new Exception('eBay order is not defined.');
        }

        return $this->_ebayOrder;
    }

    // ########################################

    public function setOrderItem(Ess_M2ePro_Model_Orders_OrderItem $orderItem)
    {
        if (is_null($orderItem->getId())) {
            throw new Exception('Item does not exist.');
        }

        $this->_orderItem = $orderItem;

        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Orders_OrderItem
     */
    public function getOrderItem()
    {
        if (is_null($this->_orderItem)) {
            throw new Exception('Item is not set.');
        }

        return $this->_orderItem;
    }

    // ########################################

    public function setAccount(Ess_M2ePro_Model_Accounts $account)
    {
        if (!$account->getId()) {
            throw new Exception('Account does not exist.');
        }

        $this->_account = $account;

        return $this;
    }

    /**
     * @throws Exception
     * @return Ess_M2ePro_Model_Accounts
     */
    public function getAccount()
    {
        if (!$this->_account) {
            throw new Exception('Account does not exist.');
        }

        return $this->_account;
    }

    // ########################################

    public function initialize(array $data)
    {
        // Init general data
        // ------------------
        $this->_data['listing_type'] = $data['listing_type'];
        $this->_data['transaction_id'] = (float)$data['transaction_id'];
        $this->_data['item_id'] = (float)$data['item_id'];
        $this->_data['item_title'] = $data['item_title'];
        $this->_data['item_sku'] = $data['item_sku'];
        $this->_data['item_condition_display_name'] = $data['item_condition_display_name'];
        $this->_data['item_site'] = $data['item_site']; // marketplace
        // ------------------

        // Init sale data
        // ------------------
        $this->_data['price'] = (float)$data['price'];
        $this->_data['buy_it_now_price'] = (float)$data['buy_it_now_price'];
        $this->_data['currency'] = $data['currency'];
        $this->_data['qty_purchased'] = (int)$data['qty_purchased'];
        $this->_data['auto_pay'] = $data['auto_pay']; // convert on server to boolean/binary
        $this->_data['variations'] = isset($data['variations']) ? serialize($data['variations']) : null;
        // ------------------
    }

    // ########################################

    public function process()
    {
        // Process item for combined order
        // ------------------
        if ($this->getEbayOrder()->isCombined() && $this->getEbayOrder()->isNew()) {
            $this->processNewCombinedItem();
        }
        // ------------------

        // ------------------
        unset($this->_data['item_site']);
        $this->_data['ebay_order_id'] = $this->getEbayOrder()->getOrder()->getId(); // ebay_order_id -> order_id
        // ------------------

        // Create order item
        // ------------------
        $this->createOrderItem();
        // ------------------
    }

    protected function processNewCombinedItem()
    {
        $ebayOrder = $this->getEbayOrder();

        $orderItem = Mage::getModel('M2ePro/Orders_OrderItem')->getCollection()
                                                              ->addFieldToFilter('item_id', $this->getData('item_id'))
                                                              ->addFieldToFilter('transaction_id', $this->getData('transaction_id'))
                                                              ->getFirstItem();

        if (is_null($orderItem->getId()) || is_null($orderItem->getData('ebay_order_id'))) {
            return;
        }

        $order = Mage::getModel('M2ePro/Orders_Order')->load($orderItem->getData('ebay_order_id'));

        if (!$order->getId()) {
            return;
        }

        if ($ebayOrder->getAccount()->isOrdersCombinedDisabled() && !$order->hasMagentoOrder()) {
            $order->deleteInstance();
        } else {
            // Parser hack -> Mage::helper('M2ePro')->__('Combined order with ID %id% was created for this transaction.');
            $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Combined order with ID %id% was created for this transaction.', array(
                '!id' => $ebayOrder->getOrder()->getData('ebay_order_id')
            ));
            $order->addWarningLogMessage($message);
        }
    }

    // ########################################

    protected function createOrderItem()
    {
        $existItem = Mage::getModel('M2ePro/Orders_OrderItem')->getCollection()
                                                              ->addFieldToFilter('ebay_order_id', $this->getData('ebay_order_id'))
                                                              ->addFieldToFilter('item_id', $this->getData('item_id'))
                                                              ->addFieldToFilter('transaction_id', $this->getData('transaction_id'))
                                                              ->getFirstItem();

        if ($existItem->getId()) {
            $existItem->addData($this->getData());
        } else {
            $existItem->setData($this->getData());
        }

        return $existItem->save();
    }

    // ########################################

    public function getItemInfoFromEbay()
    {
        $orderItem = $this->getOrderItem();

        $request = array(
            'account' => $this->getAccount()->getServerHash(),
            'item_id' => $orderItem->getData('item_id')
        );

        if ($orderItem->getOptions() && $orderItem->getData('item_sku')) {
            $request['variation_sku'] = $orderItem->getData('item_sku');
        }

        try {
            $response = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')->processVirtual('item', 'get', 'info', $request);
        } catch (Exception $exception) {
            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}
        }

        if (!isset($response['result']) || !is_array($response['result'])) {
            return array();
        }

        return $response['result'];
    }

    // ########################################
}