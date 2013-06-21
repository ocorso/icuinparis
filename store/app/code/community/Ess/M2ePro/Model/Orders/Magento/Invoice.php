<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Magento_Invoice
{
    protected $_order = NULL;

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

    public function createInvoice()
    {
        $invoice = NULL;

        try {

            $magentoOrder = $this->getOrder()->getMagentoOrder();
            if (!$magentoOrder || $magentoOrder->hasInvoices() || !$magentoOrder->canInvoice()) {
                return false;
            }

            $purchasePaymentEnabled = Mage::getStoreConfig('payment/m2epropayment/active');
            if (!$purchasePaymentEnabled) {
                // Parser hack -> Mage::helper('M2ePro')->__('Invoice was not created. Reason: Payment method "eBay Payment" is not enabled. Please enable it in magento system configuration.');
                $message = 'Invoice was not created. Reason: Payment method "eBay Payment" is not enabled. Please enable it in magento system configuration.';
                $this->getOrder()->addErrorLogMessage($message);

                return false;
            }

            if ($this->getOrder()->getAccount()->isInvoiceCreationEnabled()) {
                $invoice = $this->prepareInvoice();
            }

            $this->updateOrderStatus();

        } catch (Exception $e) {

            if (!$invoice) {
                // Parser hack -> Mage::helper('M2ePro')->__('Invoice was not created. Reason: %msg%.');
                $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Invoice was not created. Reason: %msg%.', array('msg' => $e->getMessage()));
                $this->getOrder()->addErrorLogMessage($message, $e->getTraceAsString());

                return false;
            }

            // Parser hack -> Mage::helper('M2ePro')->__('Invoice was created with error: %msg%.');
            $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Invoice was created with error: %msg%.', array('msg' => $e->getMessage()));
            $this->getOrder()->addWarningLogMessage($message, $e->getTraceAsString());
        }

        return true;
    }

    // ########################################

    protected function prepareInvoice()
    {
        // Skip invoice observer
        Mage::unregister('m2epro_skip_invoice_observer');
        Mage::register('m2epro_skip_invoice_observer', true);

        $magentoOrder = $this->getOrder()->getMagentoOrder();

        $invoice = $magentoOrder->prepareInvoice();
        $invoice->register();

        $transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)
                                                                      ->addObject($invoice->getOrder());

        $transactionSave->save();

        // Parser hack -> Mage::helper('M2ePro')->__('Invoice was created.');
        $this->getOrder()->addSuccessLogMessage('Invoice was created.');

        if ($this->getOrder()->getAccount()->isCustomerInvoiceNotificationEnabled()) {
            $invoice->sendEmail();
        }

        return $invoice;
    }

    // ########################################

    protected function updateOrderStatus()
    {
        $magentoOrder = $this->getOrder()->getMagentoOrder();

        if (!$magentoOrder->hasShipments()) {
            $magentoOrderStatus = $this->getOrder()->getAccount()->getOrderStatusOnPaymentComplete();

            if ($magentoOrderStatus != Ess_M2ePro_Model_Accounts::ORDERS_DEFAULT_STATUS_ON_PAYMENT_COMPLETE) {
                $magentoOrder->setStatus($magentoOrderStatus)
                             ->setIsCustomerNotified(false);
            } else {
                $magentoOrder->setIsInProcess(true);
            }
        }

        $magentoOrder->save();
    }

    // ########################################
}