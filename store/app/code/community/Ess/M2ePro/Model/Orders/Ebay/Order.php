<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Ebay_Order extends Mage_Core_Model_Abstract
{
    const ORDER_STATUS_NOT_MODIFIED = 0;
    const ORDER_STATUS_NEW          = 1;
    const ORDER_STATUS_UPDATED      = 2;

    // ########################################

    protected $_account = NULL;

    protected $_order = NULL;

    protected $_orderStatus = 0;

    protected $_data = array();

    protected $_items = array();

    protected $_externalTransactions = array();

    protected $_updateShippingAddress = false;

    protected $_updatePaymentData = false;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Orders_Ebay_Order');
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
            throw new Exception('Account was not set.');
        }

        return $this->_account;
    }

    // ########################################

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    // ########################################

    /**
     * @return array
     */
    public function getExternalTransactions()
    {
        return $this->_externalTransactions;
    }

    // ########################################

    public function setOrder(Ess_M2ePro_Model_Orders_Order $order)
    {
        if (!$order->getId()) {
            throw new Exception('Order does not exist.');
        }

        $this->_order = $order;
    }

    /**
     * @return Ess_M2ePro_Model_Orders_Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    // ########################################

    /**
     * @return mixed
     */
    public function getOrderStatus()
    {
        return $this->_orderStatus;
    }

    // ########################################

    public function initialize(array $data = array())
    {
        if (!count($data)) {
            throw new Exception('eBay order info is not defined.');
        }

        $this->initializeData($data);
        $this->initializeItems($data);
        $this->initializeExternalTransactions($data);
        $this->initializeOrder($data);
        $this->initializeOrderStatus();
    }

    // ########################################

    protected function initializeData(array $data = array())
    {
        // Init general data
        // ------------------
        $this->_data['account_id'] = $this->getAccount()->getId();
        $this->_data['account_mode'] = $this->getAccount()->getMode(); // do we need this?

        $this->_data['ebay_order_id'] = $data['ebay_order_id'];
        $this->_data['ebay_order_status'] = $data['ebay_order_status']; // do we need this?
        $this->_data['is_part_of_order'] = $data['is_part_of_order'];
        $this->_data['selling_manager_record_number'] = $data['selling_manager_record_number'];

        $this->_data['checkout_status'] = $data['checkout_status'];
        $this->_data['checkout_message'] = $data['checkout_message'];

        $this->_data['created_date'] = $data['created_date'];
        $this->_data['update_time'] = $data['update_time'];
        // ------------------

        // Init sale data
        // ------------------
        $this->_data['amount_paid'] = $data['amount_paid'];
        $this->_data['amount_saved'] = $data['amount_saved'];
        $this->_data['price'] = $data['price'];
        $this->_data['currency'] = $data['currency'];
        $this->_data['best_offer_sale'] = $data['best_offer_sale'];
        $this->_data['is_refund'] = $data['is_refund']; // do we need this?
        // ------------------

        // Init tax data
        // ------------------
        $this->_data['sales_tax_percent'] = $data['sales_tax_percent'];
        $this->_data['sales_tax_state'] = $data['sales_tax_state'];
        $this->_data['sales_tax_shipping_included'] = $data['sales_tax_shipping_included'];
        $this->_data['sales_tax_amount'] = $data['sales_tax_amount'];
        // ------------------

        // Init customer data
        // ------------------
        $this->_data['buyer_userid'] = $data['buyer_userid'];
        $this->_data['buyer_name'] = $data['buyer_name'];
        $this->_data['buyer_email'] = $data['buyer_email'];
        // ------------------

        // Init payment data
        // ------------------
        $this->_data['payment_used'] = $data['payment_used'];
        $this->_data['payment_status'] = $data['payment_status'];
        $this->_data['payment_status_m2e_code'] = $data['payment_status_m2e_code'];
        $this->_data['payment_hold_status'] = $data['payment_hold_status'];
        $this->_data['payment_time'] = $data['payment_time'];
        // ------------------

        // Init shipping data
        // ------------------
        $this->_data['shipping_time'] = $data['shipping_time'];
        $this->_data['shipping_address'] = $data['shipping_address'];
        $this->_data['shipping_type'] = $data['shipping_type'];
        $this->_data['shipping_status'] = $data['shipping_status'];
        $this->_data['shipping_buyer_selected'] = $data['shipping_buyer_selected'];
        $this->_data['shipping_selected_service'] = $data['shipping_selected_service'];
        $this->_data['shipping_selected_cost'] = $data['shipping_selected_cost'];
        $this->_data['shipping_tracking_details'] = $data['shipping_tracking_details'];
        $this->_data['get_it_fast'] = $data['get_it_fast'];
        // ------------------

        return $this;
    }

    // ########################################

    protected function initializeItems(array $data = array())
    {
        foreach ($data['transaction_info'] as $orderItem) {
            /** @var $item Ess_M2ePro_Model_Orders_Ebay_OrderItem */
            $item = Mage::getModel('M2ePro/Orders_Ebay_OrderItem');
            $item->setEbayOrder($this)
                 ->initialize($orderItem);

            $this->_items[] = $item;
        }

        return $this;
    }

    // ########################################

    protected function initializeExternalTransactions(array $data = array())
    {
        foreach ($data['external_transactions'] as $transaction) {
            /** @var $externalTransaction Ess_M2ePro_Model_Orders_Ebay_OrderExternalTransaction */
            $externalTransaction = Mage::getModel('M2ePro/Orders_Ebay_OrderExternalTransaction');
            $externalTransaction->setEbayOrder($this)
                                ->initialize($transaction);

            $this->_externalTransactions[] = $externalTransaction;
        }

        return $this;
    }

    // ########################################

    protected function initializeOrder(array $data = array())
    {
        $collection = Mage::getModel('M2ePro/Orders_Order')->getCollection()
                                                           ->addFieldToFilter('ebay_order_id', $data['ebay_order_id']);

        switch ($collection->getSize()) {
            case 0: // new order
            case 1: // already exist
                $this->_order = $collection->getFirstItem();
                break;
            default:
                // Parser hack -> Mage::helper('M2ePro')->__('Duplicated eBay orders with ID %id%.');
                $message = Mage::getModel('M2ePro/LogsBase')->encodeDescription('Duplicated eBay orders with ID %id%.',array('!id'=>$data['ebay_order_id']));
                throw new Exception($message);
                break;
        }

        return $this;
    }

    // ########################################

    protected function initializeOrderStatus()
    {
        $this->_orderStatus = self::ORDER_STATUS_NOT_MODIFIED;

        if (is_null($this->getOrder()->getId())) {
            $this->_orderStatus = self::ORDER_STATUS_NEW;
        } else {
            if (strtotime($this->getOrder()->getData('update_time')) != strtotime($this->getData('update_time')) ||
                strtotime($this->getOrder()->getData('payment_time')) != strtotime($this->getData('payment_time')) ||
                strtotime($this->getOrder()->getData('shipping_time')) != strtotime($this->getData('shipping_time'))) {

                $this->_orderStatus = self::ORDER_STATUS_UPDATED;
            }
        }

        return $this;
    }

    // ########################################

    protected function initializeMarketplace()
    {
        $items = $this->getItems();
        $item = $items[0];

        $marketplace = Mage::getModel('M2ePro/Marketplaces')->load($item['item_site'], 'code');

        if (!is_null($marketplaceId = $marketplace->getId())) {
            $this->_data['marketplace_id'] = $marketplaceId;
        }

        return $this;
    }

    protected function initializeShippingService()
    {
        if (is_null($marketplaceId = $this->getData('marketplace_id'))) {
            return $this;
        }

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictShipping = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_shippings');

        $dbSelect = $connRead->select()
                             ->from($tableDictShipping, 'title')
                             ->where('`marketplace_id` = ?', (int)$marketplaceId)
                             ->where('`ebay_id` = ?', $this->getData('shipping_selected_service'));
        $shipping = $connRead->fetchRow($dbSelect);

        if (is_array($shipping) && isset($shipping['title'])) {
            $this->_data['shipping_selected_service'] = $shipping['title'];
        }

        return $this;
    }

    protected function initializePaymentService()
    {
        if (is_null($marketplaceId = $this->getData('marketplace_id'))) {
            return $this;
        }

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictMarketplace = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_marketplaces');

        $dbSelect = $connRead->select()
                             ->from($tableDictMarketplace, 'payments')
                             ->where('`marketplace_id` = ?', (int)$marketplaceId);
        $marketplace = $connRead->fetchRow($dbSelect);

        if (!$marketplace) {
            return $this;
        }

        $payments = (array)json_decode($marketplace['payments'], true);

        foreach ($payments as $payment) {
            if ($payment['ebay_id'] == $this->getData('payment_used')) {
                $this->_data['payment_used'] = $payment['title'];
                break;
            }
        }

        return $this;
    }

    // ########################################

    /**
     * @return bool
     */
    public function isRefund()
    {
        return $this->getData('is_refund');
    }

    // ----------------------------------------

    /**
     * @return bool
     */
    public function isSingle()
    {
        return count($this->_items) == 1;
    }

    /**
     * @return bool
     */
    public function isCombined()
    {
        return count($this->_items) > 1;
    }

    // ----------------------------------------

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->getOrderStatus() == self::ORDER_STATUS_NEW;
    }

    /**
     * @return bool
     */
    public function isUpdated()
    {
        return $this->getOrderStatus() == self::ORDER_STATUS_UPDATED;
    }

    // ########################################

    public function process()
    {
        if (!$this->canCreateOrder()) {
            return false;
        }

        // Initialize general data
        // ------------------
        $this->initializeMarketplace();
        $this->initializeShippingService();
        $this->initializePaymentService();
        // ------------------

        // Create Order
        // ------------------
        $order = $this->createOrder();
        // ------------------

        // Process items
        // ------------------
        $this->processItems();
        // ------------------

        // Process external transactions
        // ------------------
        $this->processExternalTransactions();
        // ------------------

        // Process data updates
        // ------------------
        $this->processUpdates();
        // ------------------

        return $order;
    }

    // ########################################

    protected function processItems()
    {
        $items = $this->getItems();

        foreach ($items as $item) {
            /** @var $item Ess_M2ePro_Model_Orders_Ebay_OrderItem */
            $item->process();
        }
    }

    // ########################################

    protected function processExternalTransactions()
    {
        $transactions = $this->getExternalTransactions();

        foreach ($transactions as $transaction) {
            /** @var $transaction Ess_M2ePro_Model_Orders_Ebay_OrderExternalTransaction */
            $transaction->process();
        }
    }

    // ########################################

    /**
     * @return bool
     */
    protected function canCreateOrder()
    {
        if ($this->isRefund()) {
            return false;
        }

        if ($this->isNew() || $this->isUpdated()) {
            return true;
        }

        return false;
    }

    /**
     * @return Ess_M2ePro_Model_Orders_Order
     */
    protected function createOrder()
    {
        $order = $this->getOrder();

        if ($this->isNew()) {
            $order->setData($this->getData());
        } else {
            // Check payment status
            // ------------------
            if (!$order->isPaymentCompleted() &&
                $this->getData('payment_status_m2e_code') == Ess_M2ePro_Model_Orders_Order::PAYMENT_STATUS_COMPLETED) {

                // Parser hack -> Mage::helper('M2ePro')->__('Payment status was updated to Paid on eBay.');
                $message = 'Payment status was updated to Paid on eBay.';
                $order->addSuccessLogMessage($message);
            }
            // ------------------

            // Check shipping status
            // ------------------
            if (!$order->isShippingCompleted() &&
                $this->getData('shipping_status') == Ess_M2ePro_Model_Orders_Order::SHIPPING_STATUS_COMPLETED) {

                // Parser hack -> Mage::helper('M2ePro')->__('Shipping status was updated to Shipped on eBay.');
                $message = 'Shipping status was updated to Shipped on eBay.';
                $order->addSuccessLogMessage($message);
            }
            // ------------------

            // Check shipping address
            // ------------------
            if ($order->getShippingAddress() != $this->getData('shipping_address') && $order->hasMagentoOrder()) {
                $this->_updateShippingAddress = true;

                // Parser hack -> Mage::helper('M2ePro')->__('Buyer has changed the shipping address on eBay.');
                $message = 'Buyer has changed the shipping address on eBay.';
                $order->addWarningLogMessage($message);
            }
            // ------------------

            // Check payment method
            // ------------------
            if ($order->getData('payment_used') != $this->getData('payment_used') ||
                (!$order->hasExternalTransactions() && count($this->_externalTransactions) > 0)) {
                $this->_updatePaymentData = true;
            }
            // ------------------

            $order->addData($this->getData());
        }

        return $order->save();
    }

    // ########################################

    protected function processUpdates()
    {
        if ($this->_updateShippingAddress) {
            /** @var $magentoOrder Ess_M2ePro_Model_Orders_Magento_Order */
            $magentoOrder = Mage::getModel('M2ePro/Orders_Magento_Order');
            $magentoOrder->setOrder($this->getOrder())->updateAddress();
        }

        if ($this->_updatePaymentData) {
            /** @var $magentoOrder Ess_M2ePro_Model_Orders_Magento_Order */
            $magentoOrder = Mage::getModel('M2ePro/Orders_Magento_Order');
            $magentoOrder->setOrder($this->getOrder())->updatePaymentData();
        }
    }

    // ########################################
}