<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_EbayOrders extends Mage_Core_Model_Abstract
{
    /** Used for method getSalesStatus */
    public $loadedSale = null;

    protected $_accountModel = null;

    protected $_orderItemsCollection = null;

    protected $_orderExternalTransactionsCollection = null;

    protected $_subTotalPrice = null;

    protected $_shippingPrice = null;

    protected $_shippingSelectedService = null;

    protected $_grandTotalPrice = null;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/EbayOrders');
    }

    // ########################################

    public function isCheckoutCompleted()
    {
        return $this->getData('checkout_status') == Ess_M2ePro_Helper_Sales::CHECKOUT_STATUS_COMPLETED;
    }

    // -------------

    public function isPaymentCompleted()
    {
        return $this->getData('payment_status_m2e_code') == Ess_M2ePro_Helper_Sales::PAYMENT_STATUS_COMPLETED;
    }

    // -------------

    public function isPaymentFailed()
    {
        return $this->getData('payment_status_m2e_code') == Ess_M2ePro_Helper_Sales::PAYMENT_STATUS_UNKNOWN_ERROR;
    }

    // -------------

    public function isShippingCompleted()
    {
        return $this->getData('shipping_status') == Ess_M2ePro_Helper_Sales::SHIPPING_STATUS_COMPLETED;
    }

    // -------------

    public function isShippingMethodNotSelected()
    {
        return $this->getData('shipping_status') == Ess_M2ePro_Helper_Sales::SHIPPING_STATUS_NOT_SELECT;
    }

    // -------------

    public function isShippingInProcess()
    {
        return $this->getData('shipping_status') == Ess_M2ePro_Helper_Sales::SHIPPING_STATUS_NOT_SHIPPED;
    }

    // -------------

    public function isShippingFailed()
    {
        return $this->getData('shipping_status') == Ess_M2ePro_Helper_Sales::SHIPPING_STATUS_UNKNOWN_ERROR;
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

        if (is_null($this->_accountModel)) {
            $this->_accountModel = Mage::getModel('M2ePro/Accounts')->load($this->getData('account_id'));
        }

        return $this->_accountModel;
    }

    // ########################################

    /**
     * Save/update information about sales event.
     * On complete save return id of record
     *
     * @param  $salesEventInformation
     * @return int
     */
    public function saveSalesEvent($salesEventInformation)
    {
        $isNewEvent = true;
        // Try to save general transaction info
        if (isset($salesEventInformation['id'])) {
            // Update transaction
            $eventId = $salesEventInformation['id'];
            unset($salesEventInformation['id']);
            $this->setData($salesEventInformation)->setId($eventId)->save();
            $isNewEvent = false;
        } else {
            // Insert new transaction
            $eventId = $this->setData($salesEventInformation)->save()->getId();
        }

        $transactionInOrderModel = Mage::getModel('M2ePro/EbayOrdersItems');
        $transactionInOrderModel->saveItems($eventId, $salesEventInformation['transaction_info'], $isNewEvent);

        if (isset($salesEventInformation['external_transactions']) && count($salesEventInformation['external_transactions'])) {
            $orderExternalTransactionsModel = Mage::getModel('M2ePro/EbayOrdersExternalTransactions');
            $orderExternalTransactionsModel->saveItems($eventId, $salesEventInformation['external_transactions'], $isNewEvent);
        }

        return $eventId;
    }

    // ########################################

    public function getSalesStatus($eBayOrderId, $updateTime, $paidTime = null)
    {
        $this->loadedSale = null;

        $collection = $this->getCollection()->addFieldToFilter('ebay_order_id', $eBayOrderId); //->getData();
        if ($collection->getSize() == 0) {
            return Ess_M2ePro_Helper_Sales::TRANSACTION_STATUS_NEW;
        }

        if ($collection->getSize() > 1) {
            return Ess_M2ePro_Helper_Sales::TRANSACTION_STATUS_UNKNOWN_ERROR;
        }

        $items = $collection->getItems();
        $loadedItem = reset($items);

        if (strtotime($loadedItem->getUpdateTime()) != strtotime($updateTime) || (is_null($loadedItem->getPaymentTime()) && !is_null($paidTime))) {
            $this->loadedSale = $loadedItem;
            return Ess_M2ePro_Helper_Sales::TRANSACTION_STATUS_UPDATE;
        } else {
            return Ess_M2ePro_Helper_Sales::TRANSACTION_STATUS_NOT_MODIFY;
        }
    }

    // ########################################

    public function getMagentoOrderNumber()
    {
        if (is_null($this->getId())) {
            return false;
        }

        if (is_null($magentoOrderId = $this->getMagentoOrderId())) {
            $returnString = Mage::helper('M2ePro')->__('N/A');
        } else {
            $order = Mage::getModel('sales/order')->load($magentoOrderId);
            $orderUrl = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view/order_id/' . $magentoOrderId, null);

            $returnString = '<a href="' . $orderUrl . '" target="_blank">' . $order->getRealOrderId() . '</a>';
        }

        return $returnString;
    }

    // ########################################

    public function getAccountInfo()
    {
        if (is_null($this->getId())) {
            return false;
        }

        if (!$this->getAccount()->getId()) {
            return '<span style="color: red;">'.Mage::helper('M2ePro')->__('Deleted').'</span>';
        }

        $returnValue = '';
        $returnValue .= $this->getAccount()->getTitle();

        if ($this->getAccount()->isModeProduction()) {
            $tempString = Mage::helper('M2ePro')->__('Production');
            $returnValue .= ' ('.$tempString.')';
        } else {
            $tempString = Mage::helper('M2ePro')->__('Sandbox');
            $returnValue .= ' ('.$tempString.')';
        }

        return $returnValue;
    }

    // ########################################

    /**
     * Get complex status for eBay order-transaction
     */
    public function getOrderStatusHtml()
    {
        if (is_null($this->getId())) {
            return false;
        }

        // Checkout
        // ----------------
        $returnString = '<b>' . Mage::helper('M2ePro')->__('Checkout') . ':</b> ';

        if (!$this->isCheckoutCompleted()) {
            $returnString .= Mage::helper('M2ePro')->__('Incomplete');
        } else {
            $returnString .= '<span style="color: green;">' . Mage::helper('M2ePro')->__('Completed') . '</span>';
        }

        $returnString .= '<br />';
        // ----------------

        // Payment
        // ----------------
        $returnString .= '<b>' . Mage::helper('M2ePro')->__('Payment') . ':</b> ';

        if ($this->isPaymentCompleted()) {
            $returnString .= '<span style="color: green;">' . Mage::helper('M2ePro')->__('Paid') . '</span>';
        } else if ($this->isPaymentFailed()) {
            $returnString .= Mage::helper('M2ePro')->__('Unknown Error');
        } else {
            // TODO get payment error from eBay
            $returnString .= Mage::helper('M2ePro')->__('Waiting');
        }

        $returnString .= '<br />';
        // ----------------

        // Shipping
        // ----------------
        $returnString .= '<b>' . Mage::helper('M2ePro')->__('Shipping') . ':</b> ';

        if ($this->isShippingCompleted()) {
            $returnString .= '<span style="color: green;">' . Mage::helper('M2ePro')->__('Shipped') . '</span>';
        }
        if ($this->isShippingInProcess()) {
            $returnString .= Mage::helper('M2ePro')->__('Not Shipped');
        }
        if ($this->isShippingMethodNotSelected()) {
            $returnString .= Mage::helper('M2ePro')->__('Waiting');
        }
        if ($this->isShippingFailed()) {
            $returnString .= Mage::helper('M2ePro')->__('Unknown Error');
        }
        // ----------------

        return $returnString;
    }

    // ########################################

    public function getBuyerInfoHtml()
    {
        if (is_null($this->getId())) {
            return false;
        }

        $buyerName = $this->getBuyerName();
        $buyerEmail = $this->getBuyerEmail();
        $buyerUserId = $this->getBuyerUserid();

        if ($buyerEmail == 'Invalid Request') {
            $buyerEmail = '';
        } else {
            $buyerEmail = '&lt;' . Mage::helper('M2ePro')->escapeHtml($buyerEmail) . '&gt;';
        }

        return Mage::helper('M2ePro')->escapeHtml($buyerName) . '<br />' .
               ($buyerEmail != '' ? $buyerEmail . '<br />' : '') .
               Mage::helper('M2ePro')->escapeHtml($buyerUserId);
    }

    // ########################################

    public function getShippingAddressHtml()
    {
        if (is_null($this->getId())) {
            return false;
        }

        $shippingDetails = $this->getShippingAddress();
        if (is_string($shippingDetails)) {
            $shippingDetails = unserialize($shippingDetails);
        }
        $shippingDetailsHtml = '';
        //        if (isset($shippingDetails['firstname']) && isset($shippingDetails['lastname'])) {
        //            $shippingDetailsHtml.=$shippingDetails['firstname'] . " " . $shippingDetails['lastname'] . '<br />';
        //        }
        if (isset($shippingDetails['company']) && $shippingDetails['company'] != '') {
            $shippingDetailsHtml .= $shippingDetails['company'] . '<br />';
        }
        if (isset($shippingDetails['street']) && isset($shippingDetails['street'][0])) {
            $shippingDetailsHtml .= $shippingDetails['street'][0] . '<br />';
        }
        if (isset($shippingDetails['city']) && $shippingDetails['city'] != '') {
            $shippingDetailsHtml .= $shippingDetails['city'] . ', ';
        }
        if (isset($shippingDetails['region']) && $shippingDetails['region'] != '') {
            $shippingDetailsHtml .= $shippingDetails['region'] . ', ';
        }
        if (isset($shippingDetails['postcode']) && $shippingDetails['postcode'] != '') {
            $shippingDetailsHtml .= $shippingDetails['postcode'] . '<br />';
        }
        if (isset($shippingDetails['country_id']) && $shippingDetails['country_id'] != '') {
            $countryName = Mage::getModel('directory/country')->load($shippingDetails['country_id'])->getName();
            $shippingDetailsHtml .= $countryName . '<br />';
        }
        if (isset($shippingDetails['telephone']) && $shippingDetails['telephone'] != '') {
            $shippingDetailsHtml .= 'T:' . $shippingDetails['telephone'] . '<br />';
        }
        if (isset($shippingDetails['fax']) && $shippingDetails['fax'] != '') {
            $shippingDetailsHtml .= 'F:' . $shippingDetails['fax'];
        }

        return $shippingDetailsHtml;
    }

    // ########################################

    public function hasTax()
    {
        if (is_null($this->getId())) {
            return false;
        }
        return (float)$this->getData('sales_tax_percent') > 0;
    }

    public function hasVat()
    {
        if (is_null($this->getId())) {
            return false;
        }
        return (float)$this->getData('sales_tax_percent') > 0 && (float)$this->getData('sales_tax_amount') == 0;
    }

    // ########################################

    public function getOrderItemsCollection()
    {
        if (is_null($this->getId())) {
            return false;
        }

        if (!$this->_orderItemsCollection) {
            $collection = Mage::getModel('M2ePro/EbayOrdersItems')->getCollection();
            $collection->addFieldToFilter('ebay_order_id', $this->getId());

            $this->_orderItemsCollection = $collection;
        }

        return $this->_orderItemsCollection;
    }

    public function getExternalTransactionsCollection()
    {
        if (is_null($this->getId())) {
            return false;
        }

        if (!$this->_orderExternalTransactionsCollection) {
            $collection = Mage::getModel('M2ePro/EbayOrdersExternalTransactions')->getCollection();
            $collection->addFieldToFilter('order_id', $this->getId())
                       ->addFieldToFilter('ebay_id', array('neq' => 'SIS')) //SIS means payment method other than PayPal
                       ->setOrder('id', 'DESC')
                       ->setOrder('time', 'DESC');

            $this->_orderExternalTransactionsCollection = $collection;
        }

        return $this->_orderExternalTransactionsCollection;
    }

    public function hasExternalTransactions()
    {
        if (is_null($this->getId())) {
            return false;
        }

        $collection = $this->getExternalTransactionsCollection();
        if ($collection) {
            return count($collection->getItems()) > 0;
        }

        return false;
    }

    public function getSubtotalPrice()
    {
        if (is_null($this->getId())) {
            return false;
        }

        if (is_null($this->_subTotalPrice)) {
            $this->_subTotalPrice = round($this->getResource()->getItemsTotal($this), 2);
        }

        return $this->_subTotalPrice;
    }

    public function getShippingPrice()
    {
        if (is_null($this->getId())) {
            return false;
        }

        if (is_null($this->_shippingPrice)) {
            $this->_shippingPrice = round($this->getShippingSelectedCost(), 2);
        }

        return $this->_shippingPrice;
    }

    public function getGrandTotalPrice()
    {
        if (is_null($this->getId())) {
            return false;
        }

        if (is_null($this->_grandTotalPrice)) {
            $this->_grandTotalPrice = $this->getSubtotalPrice() + $this->getShippingPrice() + round($this->getData('sales_tax_amount'), 2);
        }

        return $this->_grandTotalPrice;
    }

    /**
     * Checking eBay for complete checkout process (select payment, shipping method).
     * When eBay checkout completed thats allow M2ePro create magento order
     *
     * @return bool ready or not for creating magento order
     */
    public function isReadyForOrder()
    {
        if (is_null($this->getId())) {
            return false;
        }

        return ($this->isCheckoutCompleted() || $this->getAccount()->isCreateOrderOnIncompleteCheckoutEnabled()) &&
                !$this->getMagentoOrderId();
    }

    /**
     * Checking eBay order for prepared of create invoice. For create invoice buyer need to complete payment
     *
     * @return bool ready or not order for create invoice
     */
    public function isReadyForInvoice()
    {
        if (is_null($this->getId())) {
            return false;
        }

        return ($this->isCheckoutCompleted() || $this->getAccount()->isCreateOrderOnIncompleteCheckoutEnabled()) &&
                !$this->isPaymentCompleted();
    }

    /**
     * Checking for prepared eBay order for create items shipping
     *
     * @return bool ready or not order for create/make shipping
     */
    public function isReadyForShipping()
    {
        if (is_null($this->getId())) {
            return false;
        }

        return ($this->isCheckoutCompleted() || $this->getAccount()->isCreateOrderOnIncompleteCheckoutEnabled()) &&
                $this->isPaymentCompleted() &&
               ($this->isShippingMethodNotSelected() || $this->isShippingInProcess());
    }

    /**
     * Return first orders item eBay itemId and transactionId
     *
     * @return array|bool false or arrray with information
     */
    public function getFirstItemIdTransactionId()
    {
        if (is_null($this->getId())) {
            return false;
        }

        $items = $this->getOrderItemsCollection()->getItems();
        $transactionItem = current(array_values($items));

        return array(
            'item_id' => $transactionItem->getItemId(),
            'transaction_id' => $transactionItem->getTransactionId()
        );
    }

    /**
     * Create magento order, invoice and shipping to connected transaction
     *
     * @param  $eBaySale
     * @param  $account
     * @return bool|int
     */
    public function createMagentoOrder($eBaySale, $account)
    {
        $magentoOrderId = 0;

        if (is_int($account)) {
            /** @var $account Ess_M2ePro_Model_Accounts */
            $account = Mage::getModel('M2ePro/Accounts')->load($account);
        }

        if (!$account || !$account->getId()) {
            return array(
                'success' => false,
                'errors' => array(
                    // Parser hack -> Mage::helper('M2ePro')->__('The specified account is not found.');
                    0 => 'The specified account is not found.'
                )
            );
        }

        $messagesSuccessList = array();
        $messagesErrorList = array();

        if (isset($eBaySale['magento_order_id']) && $eBaySale['magento_order_id'] > 0) {
            $magentoOrderId = $eBaySale['magento_order_id'];
        }

        if ($magentoOrderId == 0) {
            // No Order
            if ($eBaySale['checkout_status'] != Ess_M2ePro_Helper_Sales::CHECKOUT_STATUS_COMPLETED &&
                !$account->isCreateOrderOnIncompleteCheckoutEnabled()) {

                // Checkout not completed, forcing not enabled, can't create order
                return array(
                    'success' => false,
                    'errors' => array(
                        // Parser hack -> Mage::helper('M2ePro')->__('Order is not created. Reason: Checkout is not completed.');
                        0 => 'Order was not created. Reason: Checkout is not completed.'
                    )
                );
            }

            // Order not created.
            // Prepare information for create new order
            $information = $this->_prepareSaleToImport($eBaySale, $account);

            if ($information == false) {
                // Parser hack -> Mage::helper('M2ePro')->__('Order was not created. There\'s no associated product found for eBay item in magento catalog.');
                $message = 'Order was not created. There\'s no associated product found for eBay item in magento catalog.';
                Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($eBaySale['id'],
                                                                       $message, null,
                                                                       Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_ERROR);

                return array(
                    'success' => false,
                    'errors' => array(
                        0 => $message
                    )
                );
            }

            $resultOfCreateOrder = Mage::getModel('M2ePro/Import_Order')->createOrder($information);

            if ($resultOfCreateOrder['orderId'] > 0) {
                $magentoOrderId = $resultOfCreateOrder['orderId'];

                // Parser hack -> Mage::helper('M2ePro')->__('Magento order was created.');
                $message = 'Magento order was created.';
                $messagesSuccessList[] = $message;

                Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($eBaySale['id'], $message);
                
            } else if ($resultOfCreateOrder['message'] != '') {

                // Parser hack -> Mage::helper('M2ePro')->__('Order was not created. Reason:');
                $message = 'Order was not created. Reason: ' . $resultOfCreateOrder['message'];
                $messagesErrorList[] = $message;

                Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($eBaySale['id'],
                                                                       $message,
                                                                       $resultOfCreateOrder['message_trace'],
                                                                       Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_ERROR);
            }
        }

        if ($magentoOrderId > 0) {
            // Having magento order number
            // Check for have invoice || shipping
            // If not created before - create if possible

            // Create invoice
            if ($eBaySale['payment_status_m2e_code'] == Ess_M2ePro_Helper_Sales::PAYMENT_STATUS_COMPLETED) {
                try {
                    // Order status mapping
                    $orderNewStatusAfterInvoice = 'processing';
                    $autoInvoice = true;
                    if ($account->getOrdersStatusMode() == Ess_M2ePro_Model_Accounts::ORDERS_STATUS_MAPPING_CUSTOM) {
                        $orderNewStatusAfterInvoice = ($account->getOrdersStatusPaymentCompleted()) ? $account->getOrdersStatusPaymentCompleted() : 'processing';
                        $autoInvoice = ($account->getOrdersStatusInvoice() > 0) ? true : false;
                    }

                    if (Mage::getModel('M2ePro/Import_Invoice')->createInvoiceForOrder($magentoOrderId,  '',
                                        strpos($account->getOrdersCustomerNewSendNotifications(), 'i1') !== false,
                                        $orderNewStatusAfterInvoice, $autoInvoice)) {
                        // Parser hack -> Mage::helper('M2ePro')->__('Invoice was created.');
                        $message = 'Invoice was created.';
                        $messagesSuccessList[] = $message;
                        Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($eBaySale['id'], $message);
                    }

                } catch (Exception $exception) {
                    // Parser hack -> Mage::helper('M2ePro')->__('Invoice was not created. Reason:');
                    $message = 'Invoice was not created. Reason: '.$exception->getMessage();
                    $messagesErrorList[] = $message;
                    Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($eBaySale['id'],
                                                                           $message,
                                                                           $exception->getTraceAsString(),
                                                                           Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_ERROR);
                }
            }

            if ($eBaySale['shipping_status'] == Ess_M2ePro_Helper_Sales::SHIPPING_STATUS_COMPLETED) {
                // Ship completed
                // Create ship for mage order
                try {

                    // Order status mapping
                    $orderNewStatusAfterShipp = 'complete';
                    $autoShip = true;
                    if ($account->getOrdersStatusMode() == Ess_M2ePro_Model_Accounts::ORDERS_STATUS_MAPPING_CUSTOM) {
                        $orderNewStatusAfterShipp = ($account->getOrdersStatusShippingCompleted()) ? $account->getOrdersStatusShippingCompleted() : 'complete';
                        $autoShip = ($account->getOrdersStatusShipping() > 0) ? true : false;
                    }

                    if (Mage::getModel('M2ePro/Import_Shipping')->createShippingForOrder($magentoOrderId, '',
                                                           strpos($account->getOrdersCustomerNewSendNotifications(), 's1') !== false,
                                                           $orderNewStatusAfterShipp, $autoShip) && $autoShip) {
                        // Parser hack -> Mage::helper('M2ePro')->__('Shipment was created.');
                        $message = 'Shipment was created.';
                        $messagesSuccessList[] = $message;
                        Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($eBaySale['id'], $message);
                    }

                } catch (Exception $exception) {
                    // Parser hack -> Mage::helper('M2ePro')->__('Shipment was not created. Reason:');
                    $message = 'Shipment was not created. Reason: ' . $exception->getMessage();
                    $messagesErrorList[] = $message;
                    Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($eBaySale['id'],
                                                                           $message,
                                                                           $exception->getTraceAsString(),
                                                                           Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_ERROR);
                }
            }

            return array(
                'success' => true,
                'id' => $magentoOrderId,
                'errors' => $messagesErrorList,
                'messages' => $messagesSuccessList
            );

        }

        return array(
            'success' => false,
            'errors' => $messagesErrorList,
            'messages' => $messagesSuccessList
        );
    }

    /**
     * Generate array of information prepared for use in Order import module
     *
     * @param  $eBaySale
     * @param int|Ess_M2ePro_Model_Accounts eBay account settings loaded model
     */
    protected function _prepareSaleToImport($eBaySale, $account)
    {
        $productList = array();
        // Used for determinate what settings section use
        $hasM2eProduct = false;

        $storeIdForM2eProduct = null;

        if (is_int($account)) {
            $account = Mage::getModel('M2ePro/Accounts')->load($account);
        }

        if (!$account || !$account->getId()) {
            return false;
        }

        foreach ($eBaySale['transaction_info'] as $singleTransaction) {

            if (!$singleTransaction['product_id']) {
                continue;
            }

            if (!$hasM2eProduct && (!isset($singleTransaction['not_m2e_product']) || !$singleTransaction['not_m2e_product'])) {
                $hasM2eProduct = true;
                $storeIdForM2eProduct = $singleTransaction['store_id'];
            }

            $variationOptions = array();
            if (isset($singleTransaction['variations']) && is_string($singleTransaction['variations'])) {
                $variationOptions = unserialize($singleTransaction['variations']);
            } else if (isset($singleTransaction['variations']) && is_array($singleTransaction['variations'])) {
                $variationOptions = $singleTransaction['variations'];
            }

            $productList[] = array(
                'id' => $singleTransaction['product_id'],
                'price' => $singleTransaction['price'],
                'qty' => $singleTransaction['qty_purchased'],
                'options' => $variationOptions
            );
        }

        if ($productList == array()) {
            // No correct mapped product
            return false;
        }

        // Determinate what storeId used for create order for
        // Possible get info from m2e settings, not m2e settings and combated

        $storeId = 0;

        if ($hasM2eProduct) {
            // Having product from M2e List
            if ($account->getOrdersListingsStoreMode() == Ess_M2ePro_Model_Accounts::ORDERS_LISTINGS_STORE_MODE_NO) {
                // Get store_id from listing
                // variable calculated on
                $storeId = $storeIdForM2eProduct;
            } else {
                // From combo
                $comboValue = $account->getOrdersListingsStoreId();
                if ($comboValue <= 0 || $comboValue == '') {
                    // Empty value selected, get default storeID
                    $comboValue = Mage::helper('M2ePro/Sales')->getDefaultStoreId();
                }

                $storeId = $comboValue;
            }
        } else {
            // No m2e products, all value get from settings

            $comboValue = $account->getOrdersEbayStoreId();
            if ($comboValue <= 0 || $comboValue == '') {
                // Empty value selected, get default storeID
                $comboValue = Mage::helper('M2ePro/Sales')->getDefaultStoreId();
            }
            $storeId = $comboValue;
        }

        if (($shippingMethodName = $eBaySale['shipping_selected_service']) == 'NotSelected') {
            $shippingMethodName = Mage::helper('M2ePro')->__('Not Selected Yet');
        }

        if (($paymentMethodName = $eBaySale['payment_used']) == 'None') {
            $paymentMethodName = Mage::helper('M2ePro')->__('Not Selected Yet');
        } else if (!is_null($eBaySale['marketplace_id'])) {
            $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
            $tableDictMarketplace = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_marketplaces');

            $dbSelect = $connRead->select()
                                 ->from($tableDictMarketplace,'payments')
                                 ->where('`marketplace_id` = ?',(int)$eBaySale['marketplace_id']);
            $marketplace = $connRead->fetchRow($dbSelect);

            $payments = (array)json_decode($marketplace['payments'], true);
            foreach ($payments as $payment) {
                if ($payment['ebay_id'] == $paymentMethodName) {
                    $paymentMethodName = $payment['title'];
                    break;
                }
            }
        }

        $externalTransactions = array();
        foreach ($eBaySale['external_transactions'] as $externalTransaction) {
            //if payment method is paypal
            if ($externalTransaction['ebay_id'] != 'SIS') {
                $externalTransactions[] = $externalTransaction;
            }
        }

        $orderStatus = 'pending';
        if ($account->getOrdersStatusMode() == Ess_M2ePro_Model_Accounts::ORDERS_STATUS_MAPPING_CUSTOM) {
            $orderStatus = ($account->getOrdersStatusCheckoutCompleted()) ? $account->getOrdersStatusCheckoutCompleted() : 'pending';
        }

        return array(
            'products' => $productList,
            'billingAddress' => $eBaySale['shipping_address'],
            'shippingDetails' => array(
                'title' => $shippingMethodName,
                'price' => $eBaySale['shipping_selected_cost'],
            ),
            'paymentDetails' => array(
                'title' => $paymentMethodName,
                'ebay_order_id' => $eBaySale['ebay_order_id'],
                'ebay_account' => $account->getTitle(),
                'external_transactions' => $externalTransactions
            ),
            'checkoutMode' => $account->getOrdersCustomerMode(),
            'checkoutMessage' => (string)$eBaySale['checkout_message'],
            'customer' => array(
                'bindId' => $account->getOrdersCustomerExistId(),
                'website' => $account->getOrdersCustomerNewWebsite(),
                'group' => $account->getOrdersCustomerNewGroup(),
                'newsletter' => $account->getOrdersCustomerNewSubscribeNews(),
                // @todo to binary fields
                'notifyRegistration' => strpos($account->getOrdersCustomerNewSendNotifications(), 'a1') !== false,
                'notifyOrder' => strpos($account->getOrdersCustomerNewSendNotifications(), 'o1') !== false,
                // 'notifyInvoice' => 0,
                // 'notifyShipping' => 0,
            ),
            'currencyId' => $eBaySale['currency'],
            'storeId' => $storeId,
            'orderStatus' => $orderStatus,
            'taxPercent' => (float)$eBaySale['sales_tax_percent'],
            'taxAmount' => (float)$eBaySale['sales_tax_amount'],
            'taxIncludesShipping' => (bool)(int)$eBaySale['sales_tax_shipping_included']
        );
    }

    /**
     * @return mixed
     */
    public function markEbaySalesPaid()
    {
        $successList = array();
        $errorList = array();

        if (is_null($this->getId())) {
            // Parser hack -> Mage::helper('M2ePro')->__('Payment status for eBay order cannot be updated. Reason: eBay order does not exist.');
            $message = 'Payment status for eBay order cannot be updated. Reason: eBay order does not exist.';
            $errorList[] = $message;

            return array(
                'success' => false,
                'errors' => $errorList,
                'messages' => $successList
            );
        }

        if ($this->isPaymentCompleted()) {
            // Parser hack -> Mage::helper('M2ePro')->__('Payment status for eBay order cannot be updated. Reason: Payment status is already Paid.');
            $message = 'Payment status for eBay order cannot be updated. Reason: Payment status is already Paid.';
            $errorList[] = $message;

            Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($this->getId(), $message, null, Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_ERROR);

            return array(
                'success' => false,
                'errors' => $errorList,
                'messages' => $successList
            );
        }

        $paramsConnector = array('action' => Ess_M2ePro_Helper_Sales::SALE_ACTION_PAID);
        $result = $this->_prepareSaleUpdate($paramsConnector);

        if (is_array($result)) {
            $result['messages'] = array();
            return $result;
        }

        // Parser hack -> Mage::helper('M2ePro')->__('Payment status for eBay order was updated to Paid.');
        $message = 'Payment status for eBay order was updated to Paid.';
        Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($this->getId(), $message, null, Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_NOTICE);
        $successList[] = $message;

        // Update field in transaction table, on next synchronization
        $this->setPaymentStatusM2eCode(Ess_M2ePro_Helper_Sales::PAYMENT_STATUS_COMPLETED);
        $this->save();

        return array(
            'success' => ((int)$result > 0) ? true : false,
            'errors' => $errorList,
            'messages' => $successList
        );
    }

    /**
     * Send request to eBay to mark transaction as shipped
     *
     * @return
     */
    public function markEbaySalesShipped(array $trackingDetails = array())
    {
        $successList = array();
        $errorList = array();

        if (is_null($this->getId())) {
            // Parser hack -> Mage::helper('M2ePro')->__('Shipping status for eBay order cannot be updated. Reason: eBay order does not exist.');
            $message = 'Shipping status for eBay order cannot be updated. Reason: eBay order does not exist.';
            $errorList[] = $message;

            return array(
                'success' => false,
                'errors' => $errorList,
                'messages' => $successList
            );
        }

        if (!$this->isPaymentCompleted()) {
            // Parser hack -> Mage::helper('M2ePro')->__('Shipping status for eBay order cannot be updated. Reason: The order is not Paid.');
            $message = 'Shipping status for eBay order cannot be updated. Reason: The order is not Paid.';
            $errorList[] = $message;

            Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($this->getId(), $message, null, Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_ERROR);

            return array(
                'success' => false,
                'errors' => $errorList,
                'messages' => $successList
            );
        }

        if ($this->isShippingCompleted() && !count($trackingDetails)) {
            // Parser hack -> Mage::helper('M2ePro')->__('Shipping status for eBay order cannot be updated. Reason: Shipping status is already Shipped.');
            $message = 'Shipping status for eBay order cannot be updated. Reason: Shipping status is already Shipped.';
            $errorList[] = $message;

            Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($this->getId(), $message, null, Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_ERROR);

            return array(
                'success' => false,
                'errors' => $errorList,
                'messages' => $successList
            );
        }

        $paramsConnector = array('action' => Ess_M2ePro_Helper_Sales::SALE_ACTION_SHIPPED);
        if (count($trackingDetails)) {
            $paramsConnector = array(
                'action'          => Ess_M2ePro_Helper_Sales::SALE_ACTION_TRACKING,
                'tracking_number' => $trackingDetails['tracking_number'],
                'carrier_code'    => $trackingDetails['carrier_code']
            );

            // Parser hack -> Mage::helper('M2ePro')->__('Adding tracking number to eBay order.');
            $successList[] = 'Adding tracking number to eBay order.';
        }

        $result = $this->_prepareSaleUpdate($paramsConnector);

        if (is_array($result)) {
            $result['messages'] = array();
            return $result;
        }

        if (!$this->isShippingCompleted()) {
            $this->setShippingStatus(Ess_M2ePro_Helper_Sales::SHIPPING_STATUS_COMPLETED)->save();
            // Parser hack -> Mage::helper('M2ePro')->__('Shipping status for eBay order was updated to Shipped.');
            $successList[] = 'Shipping status for eBay order was updated to Shipped.';
        }

        foreach ($successList as $successMessage) {
            Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($this->getId(), $successMessage, null, Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_NOTICE);
        }

        return array(
            'success'  => ((int)$result > 0) ? true : false,
            'errors'   => $errorList,
            'messages' => $successList,
        );
    }

    public function convertToInfoArray()
    {
        if (is_null($this->getId())) {
            return false;
        }

        $mainDetails = $this->getData();

        $orderItems = $this->getOrderItemsCollection();
        $transactionList = array();

        foreach ($orderItems as $item) {
            $transactionList[] = $item->getData();
        }
        $mainDetails['transaction_info'] = $transactionList;

        $externalTransactions = $this->getExternalTransactionsCollection();
        $externalTransactionList = array();

        foreach ($externalTransactions as $externalTransaction) {
            $externalTransactionList[] = $externalTransaction->getData();
        }
        $mainDetails['external_transactions'] = $externalTransactionList;

        return $mainDetails;
    }

    protected function _prepareSaleUpdate($requestData)
    {
        $validActions = array(Ess_M2ePro_Helper_Sales::SALE_ACTION_TRACKING,
                              Ess_M2ePro_Helper_Sales::SALE_ACTION_PAID,
                              Ess_M2ePro_Helper_Sales::SALE_ACTION_SHIPPED);

        $loadedAccount = $this->getAccount();
        $errorList = array();

        if (!isset($requestData['action']) || !in_array($requestData['action'], $validActions) || !$loadedAccount) {
            // Parser hack -> Mage::helper('M2ePro')->__('Status for eBay Order cannot be updated. Reason: Internal Error.');
            $message = 'Status for eBay Order cannot be updated. Reason: Internal Error.';
            $errorList[] = $message;

            Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($this->getId(), $message, null, Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_ERROR);

            return array(
                'success' => false,
                'errors' => $errorList
            );
        }

        $requestData['account'] = $loadedAccount->getServerHash();

        if ($this->getIsPartOfOrder() == 1) {
            // Combined eBay transaction -> order
            $requestData['order_id'] = $this->getEbayOrderId();
        } else {
            // Single item
            // Get identify for first item
            $result = $this->getFirstItemIdTransactionId();
            if ($result == false) {
                // Parser hack -> Mage::helper('M2ePro')->__('Status for eBay Order cannot be updated. Reason: Internal Error.');
                $message = 'Status for eBay Order cannot be updated. Reason: Internal Error.';
                $errorList[] = $message;

                Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($this->getId(), $message, null, Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_ERROR);

                return array(
                    'success' => false,
                    'errors' => $errorList
                );
            }

            $requestData['transaction_id'] = $result['transaction_id'];
            $requestData['item_id'] = $result['item_id'];
        }

        $response = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')->processVirtual('sales', 'update', 'status', $requestData);

        return isset($response['result']) && $response['result'];
    }

    public function delete()
    {
        $orderIdToRemove = $this->getId();

        // Remove items
        Mage::getModel('M2ePro/EbayOrdersItems')->deleteItemsForOrder($orderIdToRemove);
        // Remove external transactions
        Mage::getModel('M2ePro/EbayOrdersExternalTransactions')->deleteTransactionForOrder($orderIdToRemove);
        // Remove Logs
        Mage::getModel('M2ePro/EbayOrdersLogs')->deleteLogsForOrder($orderIdToRemove);

        return parent::delete();
    }
}