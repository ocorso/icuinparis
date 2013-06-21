<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Invoice
{
    //####################################

    /**
     * Observer calling when created invoice for order.
     * Work only for order imported from eBay Sale
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function salesOrderInvoicePay(Varien_Event_Observer $observer)
    {
        try {

            if (Mage::registry('m2epro_skip_invoice_observer')) {
                // Not process invoice observer when set such flag
                Mage::unregister("m2epro_skip_invoice_observer");
                return;
            }

            $invoice = $observer->getEvent()->getInvoice();

            /** @var $magentoOrder Mage_Sales_Model_Order */
            $magentoOrder = $invoice->getOrder();

            if (is_null($magentoOrderId = $magentoOrder->getData('entity_id'))) {
                return;
            }

            /** @var $loadedOrder Ess_M2ePro_Model_Orders_Order */
            $loadedOrder = Mage::getModel('M2ePro/Orders_Order')->getCollection()
                                                                ->addFieldToFilter('magento_order_id', $magentoOrderId)
                                                                ->getFirstItem();

            if (!$loadedOrder || !$loadedOrder->getId()) {
                return;
            }

            $result = $loadedOrder->payOnEbay();

            if ($result) {
                $message = Mage::helper('M2ePro')->__('Payment status for eBay order was updated to Paid.');
                Mage::getSingleton('adminhtml/session')->addSuccess($message);
            } else {
                $startLink = '<a href="' . Mage::getUrl('M2ePro/adminhtml_orders/view', array('id' => $loadedOrder->getId())) . '" target="_blank">';
                $endLink = '</a>';
                $message = Mage::helper('M2ePro')->__('Payment status for eBay order was not updated. View %sl%order log%el% for more details.');

                Mage::getSingleton('adminhtml/session')->addError(str_replace(array('%sl%', '%el%'), array($startLink, $endLink), $message));
            }

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}

            return;
        }
    }

    //####################################
}