<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Track
{
    //####################################

    /**
     * Function calling after adding tracking number for magento shipping.
     * This working only for eBay imported sales
     *
     * @param Varien_Event_Observer $observer
     * @return
     */
    public function salesOrderShipmentTrackSaveAfter(Varien_Event_Observer $observer)
    {
        try {

            if (Mage::registry('m2epro_skip_track_observer')) {
                // Not process track observer when set such flag
                Mage::unregister('m2epro_skip_track_observer');
                return;
            }

            $track = $observer->getEvent()->getTrack();

            /** @var $magentoOrder Mage_Sales_Model_Order */
            $magentoOrder = $track->getShipment()->getOrder();

            if (!$track || !$magentoOrder || !$magentoOrder->getId()) {
                return;
            }

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

            $ebayCarriers = Mage::helper('M2ePro/Sales')->getEbayCarriers();
            $carrier = strtolower($track->getData('carrier_code'));
            if (isset($ebayCarriers[$carrier])) {
                $carrier = $ebayCarriers[$carrier];
            } else {
                $trackTitle = $track->getData('title');
                if (strpos($trackTitle, '.') !== false) {
                    $carrier = 'Other';
                } else {
                    $carrier = $track->getData('title');
                }
            }

            $trackingDetails = array(
                'carrier_code' => $carrier,
                'tracking_number' => $track->getData('number')
            );

            $result = $loadedOrder->shipTrackOnEbay($trackingDetails);

            if ($result) {
                $message = Mage::helper('M2ePro')->__('Tracking information has been sent on eBay.');
                Mage::getSingleton('adminhtml/session')->addSuccess($message);
            } else {
                $startLink = '<a href="' . Mage::getUrl('M2ePro/adminhtml_orders/view', array('id' => $loadedOrder->getId())) . '" target="_blank">';
                $endLink = '</a>';
                $message = Mage::helper('M2ePro')->__('Tracking information has not been sent on eBay. View %sl%order log%el% for more details.');

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