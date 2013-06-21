<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Import_Invoice
{
    /**
     * Create invoice for selected order
     *
     * @param int $orderId order id
     */
    public function createInvoiceForOrder($orderId, $commentToInvoice, $notifyCustomer = false, $orderNewStatus = 'processing', $autoInvoice = true)
    {
        if ($orderNewStatus == '' || is_null($orderNewStatus)) {
            $orderNewStatus = 'processing';
        }
        // Load Order
        $order = Mage::getModel('sales/order')->load($orderId);

        if (is_null($order->getId())) {
            // Trying create invoice for nonexistent order
            return false;
        }

        $invoiceCollection = $order->getInvoiceCollection();
        if (count($invoiceCollection) > 0) {
            // Invoice already created, skip creating
            return false;
        }

        if ($autoInvoice) {

            // Check for Enabled Method M2eProPayment
            $purchasePaymentEnabled = Mage::getStoreConfig('payment/m2epropayment/active');
            if ($purchasePaymentEnabled != true) {
                throw new LogicException("Payment method 'eBay Payment' is not enabled. Please enable it in magento system configuration.");
            }

            // Conver order to invoice
            $convertor = Mage::getModel('sales/convert_order');
            /** @var $invoice Mage_Sales_Model_Order_Invoice */
            $invoice = $convertor->toInvoice($order);

            /* @var $orderItem Mage_Sales_Model_Order_Item */
            foreach ($order->getAllItems() as $orderItem) {

                if (!$orderItem->isDummy() && !$orderItem->getQtyToInvoice() && $orderItem->getLockedDoInvoice()) {
                    continue;
                }

                if ($order->getForcedDoShipmentWithInvoice() && $orderItem->getLockedDoShip()) {
                    continue;
                }

                $item = $convertor->itemToInvoiceItem($orderItem);

                if ($orderItem->isDummy()) {
                    $qty = 1;
                } else {
                    $qty = $orderItem->getQtyToInvoice();
                }

                $item->setQty($qty);
                $invoice->addItem($item);
            }
            $invoice->collectTotals();

            // Skip invoice observer
            Mage::unregister('m2epro_skip_invoice_observer');
            Mage::register('m2epro_skip_invoice_observer', true);

            // Text, Need Notify customer
            $invoice->addComment($commentToInvoice, $notifyCustomer);
            $invoice->register();
            $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());

            $transactionSave->save();

            if ($notifyCustomer) {
                $invoice->sendEmail($notifyCustomer, $commentToInvoice);
            }
        }

        // @todo required addition testing for special events connected to setIsInProcess
        $shipmentCollection = $order->getShipmentsCollection();

        if (count($shipmentCollection) == 0) {
            // Don't have shipping, so possible to change order status
            if ($orderNewStatus != 'processing') {
                $order->addStatusToHistory($orderNewStatus, '', false);
            } else {
                $order->setIsInProcess(true);
            }
        }

        $order->save();
        return true;
    }
}