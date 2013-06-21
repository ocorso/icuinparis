<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Magento_Order
{
    protected $_order = NULL;

    /** @var $_quote Ess_M2ePro_Model_Orders_Magento_Quote */
    protected $_quote = NULL;

    // ########################################

    public function setOrder(Ess_M2ePro_Model_Orders_Order $order)
    {
        $this->_order = $order;

        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Orders_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order) || !$this->_order->getId()) {
            throw new Exception('Order was not set.');
        }

        return $this->_order;
    }

    // ########################################

    public function createOrder()
    {
        $result = false;

        try {

            $this->processTransactions();

            // Prepare quote
            // ----------------
            /** @var $quoteModel Ess_M2ePro_Model_Orders_Magento_Quote */
            $quoteModel = Mage::getModel('M2ePro/Orders_Magento_Quote');
            $quoteModel->setOrder($this->getOrder());
            $this->_quote = $quoteModel->prepareQuote();
            // ----------------

            $this->createMagentoOrder();

            if (!$this->getOrder()->hasMagentoOrder()) {
                // Parser hack -> Mage::helper('M2ePro')->__('Magento Order was not created. Reason: unknown.');
                $message = 'Magento Order was not created. Reason: unknown.';
                $this->getOrder()->addErrorLogMessage($message);

                $result = false;
            } else {
                // Parser hack -> Mage::helper('M2ePro')->__('Magento Order was created.');
                $message = 'Magento Order was created.';
                $this->getOrder()->addSuccessLogMessage($message);

                $result = true;
            }

        } catch (Exception $e) {

            if (!$this->getOrder()->hasMagentoOrder()) {
                // Parser hack -> Mage::helper('M2ePro')->__('Magento Order was not created. Reason: %msg%');
                $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Magento Order was not created. Reason: %msg%', array('msg' => $e->getMessage()));
                $this->getOrder()->addErrorLogMessage($message, $e->getTraceAsString());

                $result = false;
            } else {
                // Parser hack -> Mage::helper('M2ePro')->__('Magento Order was created with error: %msg%');
                $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Magento Order was created with error: %msg%', array('msg' => $e->getMessage()));
                $this->getOrder()->addWarningLogMessage($message, $e->getTraceAsString());

                $result = true;
            }

        }

        $this->rollbackChanges();

        return $result;
    }

    // ########################################

    protected  function createMagentoOrder()
    {
        $magentoQuote = $this->_quote->getQuote();

        $service = Mage::getModel('sales/service_quote', $magentoQuote);

        if ($service && method_exists($service, 'submitAll')) {
            $service->submitAll();
            $orderObj = $service->getOrder();
        } else {
            // Magento version 1.4.0.x

            $convertQuoteObj = Mage::getSingleton('sales/convert_quote');
            /** @var $orderObj Mage_Sales_Model_Order */
            $orderObj = $convertQuoteObj->addressToOrder($magentoQuote->getShippingAddress());

            $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($magentoQuote->getBillingAddress()));
            $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($magentoQuote->getShippingAddress()));
            $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($magentoQuote->getPayment()));

            $items = $magentoQuote->getShippingAddress()->getAllItems();

            foreach ($items as $item) {
                //@var $item Mage_Sales_Model_Quote_Item
                $orderItem = $convertQuoteObj->itemToOrderItem($item);
                if ($item->getParentItem()) {
                    $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
                }
                $orderObj->addItem($orderItem);
            }

            $orderObj->setCanShipPartiallyItem(false);
            $orderObj->place();
        }
        // ----------------

        // ----------------
        $orderObj->setStatus($this->getOrder()->getAccount()->getOrderStatusOnCheckoutComplete())
                 ->save();

        $this->getOrder()->setData('magento_order_id', $orderObj->getId())
                         ->save();

        $magentoQuote->setIsActive(false)
                     ->save();
        // ----------------

        $this->processOrderNotifications($orderObj);

		return $orderObj;
    }

    // ########################################

    protected function processOrderNotifications(Mage_Sales_Model_Order $magentoOrder)
    {
        if ($this->getOrder()->getAccount()->isCustomerOrderNotificationEnabled()) {
            $magentoOrder->sendNewOrderEmail();
        }

        $checkoutMessage = $this->getOrder()->getData('checkout_message');
        $orderCommentsArray = $this->_quote->getOrderComments();

        if (!$checkoutMessage && !count($orderCommentsArray)) {
            return;
        }

        $comments = '<br /><b><u>' . Mage::helper('M2ePro')->__('M2E Pro Notes') . ':</u></b><br /><br />';
        if ($checkoutMessage) {
            $comments .= '<b>' . Mage::helper('M2ePro')->__('Checkout Message From Buyer') . ': </b>';
            $comments .= $checkoutMessage . '<br />';
        }

        foreach ($orderCommentsArray as $comment) {
            $comments .= $comment . '<br /><br />';
        }

        $magentoOrder->addStatusHistoryComment($comments)->save();
    }

    // ########################################

    protected function processTransactions()
    {
        if ($this->getOrder()->isSingle() || $this->getOrder()->getAccount()->isOrdersCombinedDisabled()) {
            return;
        }

        $transactions = $this->getOrder()->getCombinedTransactionsCollection()->getItems();

        foreach ($transactions as $transaction) {
            /** @var $magentoOrder Mage_Sales_Model_Order */
            if (!$magentoOrder = $transaction->getMagentoOrder()) {
                continue;
            }

            if ($isCancelSuccess = $magentoOrder->canCancel()) {
                try {
                    $magentoOrder->cancel()->save();
                } catch (Exception $e) {
                    $isCancelSuccess = false;
                }
            }

            if ($isCancelSuccess) {
                // Parser hack -> Mage::helper('M2ePro')->__('Magento Order was cancelled for this transaction.');
                $transaction->addWarningLogMessage('Magento Order was cancelled for this transaction.');
            } else {
                // Parser hack -> Mage::helper('M2ePro')->__('Magento Order cannot be cancelled for this transaction.');
                $transaction->addWarningLogMessage('Magento Order cannot be cancelled for this transaction.');
            }
        }
    }

    // ########################################

    protected function rollbackChanges()
    {
        // Rollback store settings
        // ----------------
        if (!is_null($this->_quote->getStoreShippingTaxClass())) {
            $this->_quote->getQuote()->getStore()->setConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $this->_quote->getStoreShippingTaxClass());
        }
        // ----------------
    }

    // ########################################

    /**
     * Updates address info for exist order
     *
     * @return boolean
     */
    public function updateAddress()
    {
        $magentoOrder = $this->getOrder()->getMagentoOrder();
        if (!$magentoOrder) {
            return false;
        }

        $addressInfo = $this->getOrder()->getPreparedShippingAddress();
        unset($addressInfo['address_id']);

        try {
            $billingAddress = $magentoOrder->getBillingAddress();
            $billingAddress->addData($addressInfo);
            $billingAddress->implodeStreetAddress()->save();

            $shippingAddress = $magentoOrder->getShippingAddress();
            $shippingAddress->addData($addressInfo);
            $shippingAddress->implodeStreetAddress()->save();

            // Parser hack -> Mage::helper('M2ePro')->__('Shipping address for Magento Order was updated.');
            $message = 'Shipping address for Magento Order was updated.';
            $this->getOrder()->addSuccessLogMessage($message);
        } catch (Exception $e) {
            // Parser hack -> Mage::helper('M2ePro')->__('Shipping address for Magento Order was not updated. Reason: %msg%.');
            $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Shipping address for Magento Order was not updated. Reason: %msg%.', array('msg' => $e->getMessage()));
            $this->getOrder()->addWarningLogMessage($message, $e->getTraceAsString());

            return false;
        }

        return true;
    }

    // ########################################

    /**
     * Updates payment method title for exist order
     *
     * @return boolean
     */
    public function updatePaymentData()
    {
        $magentoOrder = $this->getOrder()->getMagentoOrder();
        if (!$magentoOrder) {
            return false;
        }

        try {

            $newPaymentData = array(
                'ebay_payment_method'   => $this->getOrder()->getData('payment_used'),
                'ebay_order_id'         => $this->getOrder()->getData('ebay_order_id'),
                'external_transactions' => $this->getOrder()->getPreparedExternalTransactions()
            );

            $payment = $magentoOrder->getPayment();

            if (!$payment) {
                return false;
            }

            $payment->setData('additional_data', serialize($newPaymentData))->save();

            // Parser hack -> Mage::helper('M2ePro')->__('Payment data for Magento Order was updated.');
            $message = 'Payment data for Magento Order was updated.';
            $this->getOrder()->addSuccessLogMessage($message);

        } catch (Exception $e) {
            // Parser hack -> Mage::helper('M2ePro')->__('Payment data for Magento Order was not updated. Reason: %msg%.');
            $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Payment data for Magento Order was not updated. Reason: %msg%.', array('msg' => $e->getMessage()));
            $this->getOrder()->addWarningLogMessage($message, $e->getTraceAsString());

            return false;
        }

        return true;
    }

    // ########################################
}