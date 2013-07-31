<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Import_Shipping
{
    public function createShippingForOrder($orderId, $commentToShipping, $notifyCustomer = false, $orderNewStatus = 'complete', $autoShip = true)
    {
        // Load Order
        $order = Mage::getModel('sales/order')->load($orderId);

        $shipmentCollection = $order->getShipmentsCollection();
        if (count($shipmentCollection) > 0) {
            // Shipping already created, skip creating
            return false;
        }

        /**
         * Check shipment create availability
         */
        if (!$order->canShip()) {
            return false;
        }

        if ($autoShip) {

            // Convert order to shipping
            /** @var $convertor Mage_Sales_Model_Convert_Order */
            $convertor = Mage::getModel('sales/convert_order');
            /** @var $shipment Mage_Sales_Model_Order_Shipment */
            $shipment = $convertor->toShipment($order);

            foreach ($order->getAllItems() as $orderItem) {
                if (!$orderItem->isDummy(true) && !$orderItem->getQtyToShip()) {
                    continue;
                }

                if ($orderItem->getIsVirtual()) {
                    continue;
                }
                $item = $convertor->itemToShipmentItem($orderItem);

                if ($orderItem->isDummy(true)) {
                    $qty = 1;
                } else {
                    $qty = $orderItem->getQtyToShip();
                }

                $item->setQty($qty);
                $shipment->addItem($item);
            }

            Mage::unregister('m2epro_skip_shipping_observer');
            Mage::register('m2epro_skip_shipping_observer', true);

            $shipment->register();

            if ($commentToShipping != '') {
                $shipment->addComment($commentToShipping, $notifyCustomer);
            }

            $shipment->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
        }

        if (method_exists(new Mage(), 'getVersionInfo')) {
            // Need this code to finish order after shipping on magento greater that 1.4
            $order->addStatusHistoryComment('', $orderNewStatus)->setIsCustomerNotified(false);
        } else {
            $order->setStatus($orderNewStatus);
        }
        $order->save();
        
        return true;
    }
}