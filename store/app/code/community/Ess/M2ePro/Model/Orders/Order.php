<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Order extends Mage_Core_Model_Abstract
{
    const CHECKOUT_STATUS_INCOMPLETE = 0;
    const CHECKOUT_STATUS_COMPLETED  = 1;

    const PAYMENT_STATUS_NOT_SELECT    = 0;
    const PAYMENT_STATUS_PROBLEM       = 1;
    const PAYMENT_STATUS_NO_PAY        = 2;
    const PAYMENT_STATUS_COMPLETED     = 3;
    const PAYMENT_STATUS_UNKNOWN_ERROR = 4;

    const SHIPPING_STATUS_NOT_SELECT    = 0;
    const SHIPPING_STATUS_NOT_SHIPPED   = 1;
    const SHIPPING_STATUS_COMPLETED     = 2;
    const SHIPPING_STATUS_UNKNOWN_ERROR = 3;

    // ########################################
    
    protected $_account = NULL;

    protected $_magentoOrder = NULL;

    protected $_itemsCollection = NULL;

    protected $_externalTransactionsCollection = NULL;

    protected $_logsCollection = NULL;

    protected $_subTotalPrice = NULL;

    protected $_grandTotalPrice = NULL;

    protected $_preparedShippingAddress = NULL;

    protected $_preparedExternalTransactions = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Orders_Order');
    }

    // ########################################

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Accounts
     */
    public function getAccount()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        if (is_null($this->_account)) {
            $this->_account = Mage::getModel('M2ePro/Accounts')->loadInstance($this->getData('account_id'));
        }

        return $this->_account;
    }

    // ########################################

    /**
     * @throws Exception
     * @return Ess_M2ePro_Model_Mysql4_Orders_OrderItem_Collection
     */
    public function getItemsCollection()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        if (is_null($this->_itemsCollection)) {
            $collection = Mage::getModel('M2ePro/Orders_OrderItem')->getCollection();
            $collection->addFieldToFilter('ebay_order_id', $this->getId());

            $this->_itemsCollection = $collection;
        }

        return $this->_itemsCollection;
    }

    // ########################################

    public function hasExternalTransactions()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        return $this->getExternalTransactionsCollection()->getSize() > 0;
    }

    /**
     * @throws Exception
     * @return Ess_M2ePro_Model_Mysql4_Orders_OrderExternalTransaction_Collection
     */
    public function getExternalTransactionsCollection()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        if (is_null($this->_externalTransactionsCollection)) {
            $collection = Mage::getModel('M2ePro/Orders_OrderExternalTransaction')->getCollection();
            $collection->addFieldToFilter('order_id', $this->getId())
                       ->addFieldToFilter('ebay_id', array('neq' => 'SIS')); //SIS means payment method other than PayPal

            $this->_externalTransactionsCollection = $collection;
        }

        return $this->_externalTransactionsCollection;
    }

    public function getPreparedExternalTransactions()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        if (is_null($this->_preparedExternalTransactions)) {
            $this->_preparedExternalTransactions = array();

            foreach ($this->getExternalTransactionsCollection() as $externalTransaction) {
                $this->_preparedExternalTransactions[] = array(
                    'ebay_id' => $externalTransaction->getData('ebay_id'),
                    'fee'     => $externalTransaction->getData('fee'),
                    'sum'     => $externalTransaction->getData('sum'),
                    'time'    => $externalTransaction->getData('time')
                );
            }
        }

        return $this->_preparedExternalTransactions;
    }

    // ########################################

    public function getLogsCollection()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        if (is_null($this->_logsCollection)) {
            $collection = Mage::getModel('M2ePro/Orders_OrderLog')->getCollection()
                                                                  ->addFieldToFilter('order_id', $this->getId());
            $this->_logsCollection = $collection;
        }

        return $this->_logsCollection;
    }

    public function addSuccessLogMessage($message, $exceptionTrace = NULL)
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        Mage::getModel('M2ePro/Orders_OrderLog')->addSuccessMessage($this->getId(), $message, $exceptionTrace);
    }

    public function addNoticeLogMessage($message, $exceptionTrace = NULL)
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        Mage::getModel('M2ePro/Orders_OrderLog')->addNoticeMessage($this->getId(), $message, $exceptionTrace);
    }

    public function addWarningLogMessage($message, $exceptionTrace = NULL)
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        Mage::getModel('M2ePro/Orders_OrderLog')->addWarningMessage($this->getId(), $message, $exceptionTrace);
    }

    public function addErrorLogMessage($message, $exceptionTrace = NULL)
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        Mage::getModel('M2ePro/Orders_OrderLog')->addErrorMessage($this->getId(), $message, $exceptionTrace);
    }

    // ########################################

    public function getPreparedShippingAddress()
    {
        if (is_null($this->_preparedShippingAddress)) {
            $addressInformation = $this->getShippingAddress(true);

            if (!isset($addressInformation['region_id']) || $addressInformation['region_id'] == null) {
                $addressInformation['region_id'] = 1;
            }
            if (!isset($addressInformation['region']) || $addressInformation['region'] == null) {
                $addressInformation['region'] = '';
            }

            $region = Mage::getModel('directory/region')->getCollection()
                                                        ->addCountryFilter($addressInformation['country_id'])
                                                        ->addRegionCodeFilter($addressInformation['region_id'])
                                                        ->getFirstItem();
            $addressInformation['region_id'] = $region->getData('region_id') ? $region->getData('region_id') : 1;

            if (!isset($addressInformation['postcode']) || $addressInformation['postcode'] == '') {
                $addressInformation['postcode'] = '0000';
            }
            if (!isset($addressInformation['telephone']) || $addressInformation['telephone'] == '') {
                $addressInformation['telephone'] = '0000000';
            }

            $addressInformation['customer_password'] = $addressInformation['confirm_password'] = Mage::helper('core')->getRandomString(6);
            $addressInformation['save_in_address_book'] = 0;

            if (!isset($addressInformation['firstname']) || $addressInformation['firstname'] == '') {
                $addressInformation['firstname'] = 'Name';
            }
            if (!isset($addressInformation['lastname']) || $addressInformation['lastname'] == '') {
                $addressInformation['lastname'] = 'Surname';
            }

            $this->_preparedShippingAddress = $addressInformation;
        }

        return $this->_preparedShippingAddress;
    }

    public function getShippingAddress($asArray = false)
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        $shippingAddress = $this->getData('shipping_address');

        if (!$shippingAddress) {
            return $asArray ? array() : $shippingAddress;
        }

        return $asArray ? unserialize($shippingAddress) : $shippingAddress;
    }

    public function getShippingTrackingDetails($asArray = false)
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        $trackingDetails = $this->getData('shipping_tracking_details');

        if (!$trackingDetails) {
            return $asArray ? array() : $trackingDetails;
        }

        return $asArray ? unserialize($trackingDetails) : $trackingDetails;
    }

    // ########################################

    public function hasMagentoOrder()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        return !is_null($this->getData('magento_order_id'));
    }

    /**
     * @throws Exception
     * @return bool|Mage_Sales_Model_Order
     */
    public function getMagentoOrder()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        if (!$this->hasMagentoOrder()) {
            return NULL;
        }

        if (is_null($this->_magentoOrder)) {
            $magentoOrder = Mage::getModel('sales/order')->load($this->getData('magento_order_id'));

            if (is_null($magentoOrder->getId())) {
                return NULL;
            }

            $this->_magentoOrder = $magentoOrder;
        }

        return $this->_magentoOrder;
    }

    // ########################################

    public function isSingle()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        return $this->getItemsCollection()->getSize() == 1;
    }

    public function isCombined()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        return $this->getItemsCollection()->getSize() > 1;
    }

    //-------------

    public function getCombinedTransactionsCollection()
    {
        if (!$this->isCombined()) {
            throw new Exception('Method is not applicable to single-line orders.');
        }

        $transactionOrderIds = array();
        foreach ($this->getItemsCollection()->getItems() as $orderItem) {
            $transactionOrderIds[] = $orderItem->getData('item_id') . '-' . $orderItem->getData('transaction_id');
        }

        return $this->getCollection()
                    ->addFieldToFilter('ebay_order_id', array('in' => $transactionOrderIds));
    }

    private function isTransactionHasMagentoOrder()
    {
        if (!$this->isCombined()) {
            throw new Exception('Method is not applicable to single-line orders.');
        }

        $transactionsCollection = $this->getCombinedTransactionsCollection();
        $transactionsCollection->addFieldToFilter('magento_order_id', array('notnull' => true));

        return $transactionsCollection->getSize() > 0;
    }

    // ########################################

    public function getCheckoutStatus()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        return $this->getData('checkout_status');
    }

    public function isCheckoutCompleted()
    {
        return $this->getCheckoutStatus() == self::CHECKOUT_STATUS_COMPLETED;
    }

    //-------------

    public function getPaymentStatusM2eCode()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        return $this->getData('payment_status_m2e_code');
    }

    public function isPaymentCompleted()
    {
        return $this->getPaymentStatusM2eCode() == self::PAYMENT_STATUS_COMPLETED;
    }

    public function isPaymentFailed()
    {
        return $this->getPaymentStatusM2eCode() == self::PAYMENT_STATUS_UNKNOWN_ERROR;
    }

    //-------------

    public function getShippingStatus()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        return $this->getData('shipping_status');
    }

    public function isShippingCompleted()
    {
        return $this->getShippingStatus() == self::SHIPPING_STATUS_COMPLETED;
    }

    public function isShippingMethodNotSelected()
    {
        return $this->getShippingStatus() == self::SHIPPING_STATUS_NOT_SELECT;
    }

    public function isShippingInProcess()
    {
        return $this->getShippingStatus() == self::SHIPPING_STATUS_NOT_SHIPPED;
    }

    public function isShippingFailed()
    {
        return $this->getShippingStatus() == self::SHIPPING_STATUS_UNKNOWN_ERROR;
    }

    // ########################################

    public function hasTax()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        return $this->getData('sales_tax_percent') > 0 && $this->getData('sales_tax_amount') > 0;
    }

    public function hasVat()
    {
        return $this->getData('sales_tax_percent') > 0 && $this->getData('sales_tax_amount') == 0;
    }

    public function isShippingIncludesTax()
    {
        if (is_null($this->getId())) {
            throw new Exception('Order does not exist. Probably it was deleted.');
        }

        $this->getData('sales_tax_shipping_included') == 0;
    }

    // ########################################

    /**
     * @return bool
     */
    public function canCreateMagentoOrder($isFrontend = false)
    {
        if (!is_null($this->getData('magento_order_id'))) {
            return false;
        }

        if ($this->getAccount()->isOrdersListingsModeDisabled() &&
            $this->getAccount()->isOrdersEbayModeDisabled()) {
            return false;
        }

        if (!$this->isCheckoutCompleted() && $this->getAccount()->isOrdersCreationOnCheckoutStatusCompleteEnabled()) {
            return false;
        }

        if (!$this->isPaymentCompleted() && $this->getAccount()->isOrdersCreationOnPaymentStatusCompleteEnabled()) {
            return false;
        }

        if ($isFrontend) {
            return true;
        }

        if ($this->isCombined() && $this->isTransactionHasMagentoOrder() && $this->getAccount()->isOrdersCombinedDisabled()) {
            // Parser hack -> Mage::helper('M2ePro')->__('Magento order was not created. Reason: Combined Payment orders creation is disabled in account settings.');
            $this->addWarningLogMessage('Magento Order was not created. Reason: Combined Payment orders creation is disabled in account settings.');
            return false;
        }

        return true;
    }

    public function createMagentoOrder()
    {
        if (is_null($this->getId())) {
             throw new Exception('Order does not exist.');
        }

        if (!$this->canCreateMagentoOrder()) {
            return false;
        }

        /** @var $newMagentoOrder Ess_M2ePro_Model_Orders_Magento_Order */
        $newMagentoOrder = Mage::getModel('M2ePro/Orders_Magento_Order');
        $newMagentoOrder->setOrder($this);

        return $newMagentoOrder->createOrder();
    }

    // ########################################

    public function createPaymentTransactionForMagentoOrder()
    {
        if (is_null($this->getId())) {
             throw new Exception('Order does not exist.');
        }

        /** @var $paymentTransaction Ess_M2ePro_Model_Orders_Magento_PaymentTransaction */
        $paymentTransaction = Mage::getModel('M2ePro/Orders_Magento_PaymentTransaction');
        $paymentTransaction->setOrder($this);

        return $paymentTransaction->createPaymentTransaction();
    }

    public function createShipmentForMagentoOrder()
    {
        if (is_null($this->getId())) {
             throw new Exception('Order does not exist.');
        }

        if (!$this->isShippingCompleted()) {
            return false;
        }

        /** @var $shipment Ess_M2ePro_Model_Orders_Magento_Shipment */
        $shipment = Mage::getModel('M2ePro/Orders_Magento_Shipment');
        $shipment->setOrder($this);

        return $shipment->createShipment();
    }

    public function createInvoiceForMagentoOrder()
    {
        if (is_null($this->getId())) {
             throw new Exception('Order does not exist.');
        }

        if (!$this->isPaymentCompleted()) {
            return false;
        }

        /** @var $invoice Ess_M2ePro_Model_Orders_Magento_Invoice */
        $invoice = Mage::getModel('M2ePro/Orders_Magento_Invoice');
        $invoice->setOrder($this);

        return $invoice->createInvoice();
    }

    // ########################################

    public function getGrandTotalPrice()
    {
        if (is_null($this->getId())) {
             throw new Exception('Order does not exist.');
        }

        if (is_null($this->_grandTotalPrice)) {
            $this->_grandTotalPrice = $this->getSubtotalPrice() + $this->getShippingPrice() + round((float)$this->getData('sales_tax_amount'), 2);
        }

        return $this->_grandTotalPrice;
    }

    public function getSubtotalPrice()
    {
        if (is_null($this->getId())) {
             throw new Exception('Order does not exist.');
        }

        if (is_null($this->_subTotalPrice)) {
            $itemsTotalPrice = Mage::getModel('M2ePro/Orders_OrderItem')->setOrder($this)
                                                                        ->getItemsTotalPrice();

            $this->_subTotalPrice = $itemsTotalPrice;
        }

        return $this->_subTotalPrice;
    }

    public function getShippingPrice()
    {
        if (is_null($this->getId())) {
             throw new Exception('Order does not exist.');
        }

        return round((float)$this->getData('shipping_selected_cost'), 2);
    }

    // ########################################

    /**
     * @return bool
     */
    public function canPayOnEbay()
    {
        if (is_null($this->getId())) {
             throw new Exception('Order does not exist.');
        }

        return !$this->isPaymentCompleted();
    }

    public function payOnEbay(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connectors_Ebay_Order_Dispatcher::ACTION_PAY, $params);
    }

    //----------------

    /**
     * @return bool
     */
    public function canShipOnEbay()
    {
        if (is_null($this->getId())) {
             throw new Exception('Order does not exist.');
        }

        return $this->isPaymentCompleted() &&
              ($this->isShippingMethodNotSelected() || $this->isShippingInProcess());
    }

    public function shipOnEbay(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connectors_Ebay_Order_Dispatcher::ACTION_SHIP, $params);
    }

    public function shipTrackOnEbay(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connectors_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK, $params);
    }

    //----------------

    protected function processDispatcher($action, array $params = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Order does not exist.');
        }

        return Mage::getModel('M2ePro/Connectors_Ebay_Order_Dispatcher')->process($action, $this, $params);
    }

    // ########################################

    public function deleteInstance()
    {
        if (is_null($this->getId())) {
             throw new Exception('Order does not exist.');
        }

        $orderItemsTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_orders_items');
        Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                ->delete($orderItemsTable,array('ebay_order_id = ?'=>$this->getId()));

        $orderExternalTransactionsTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_orders_external_transactions');
        Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                ->delete($orderExternalTransactionsTable,array('order_id = ?'=>$this->getId()));

        $orderLogsTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_orders_logs');
        Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                ->delete($orderLogsTable,array('order_id = ?'=>$this->getId()));

        return $this->delete();
    }

    // ########################################
}