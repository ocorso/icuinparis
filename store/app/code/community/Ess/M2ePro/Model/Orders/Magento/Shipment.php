<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Magento_Shipment
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

    public function createShipment()
    {
        $shipment = NULL;

        try {

            $magentoOrder = $this->getOrder()->getMagentoOrder();
            if (!$magentoOrder) {
                return false;
            }

            if ($this->getOrder()->getAccount()->isShipmentCreationEnabled() &&
                $magentoOrder->canShip() && !$magentoOrder->hasShipments()) {

                $shipment = $this->prepareShipment();
            }

            if ($shipment || $magentoOrder->hasShipments()) {
                $this->addTrack($shipment);
            }

            $this->updateOrderStatus();

        } catch (Exception $e) {

            if (!$shipment) {
                // Parser hack -> Mage::helper('M2ePro')->__('Shipment was not created. Reason: %msg%.');
                $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Shipment was not created. Reason: %msg%.', array('msg' => $e->getMessage()));
                $this->getOrder()->addErrorLogMessage($message, $e->getTraceAsString());

                return false;
            }

            // Parser hack -> Mage::helper('M2ePro')->__('Shipment was created with error: %msg%.');
            $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Shipment was created with error: %msg%.', array('msg' => $e->getMessage()));
            $this->getOrder()->addWarningLogMessage($message, $e->getTraceAsString());
        }

        return true;
    }

    // ########################################

    protected function prepareShipment()
    {
        $this->prepareObservers();

        $magentoOrder = $this->getOrder()->getMagentoOrder();
        if (!$magentoOrder) {
            return false;
        }

        $shipment = $magentoOrder->prepareShipment();
        $shipment->register();

        Mage::getModel('core/resource_transaction')->addObject($shipment)
                                                   ->addObject($shipment->getOrder())
                                                   ->save();

        $magentoOrder->setIsInProcess(true)
                     ->save();

        // Parser hack -> Mage::helper('M2ePro')->__('Shipment was created.');
        $this->getOrder()->addSuccessLogMessage('Shipment was created.');

        return $shipment;
    }

    // ########################################

    public function addTrack($shipment)
    {
        $trackingDetails = $this->getEbayTrackingDetails();
        if (!count($trackingDetails)) {
            return false;
        }

        if (!$shipment) {
            $shipment = $this->getOrder()->getMagentoOrder()->getShipmentsCollection()->getFirstItem();
        }

        if (!$shipment || !$shipment->getId()) {
            return false;
        }

        $this->prepareObservers(true);

        foreach ($trackingDetails as $trackingDetail) {
            $track = Mage::getModel('sales/order_shipment_track')->setNumber($trackingDetail['number'])
                                                                 ->setTitle($trackingDetail['title'])
                                                                 ->setCarrierCode($this->getCarrierCodeByTitle($trackingDetail['title']));
            $shipment->addTrack($track)->save();
        }

        // Parser hack -> Mage::helper('M2ePro')->__('Tracking details has been imported from eBay.');
        $this->getOrder()->addSuccessLogMessage('Tracking details has been imported from eBay.');

        return true;
    }

    /**
     * @return array
     */
    protected function getEbayTrackingDetails()
    {
        $trackingDetails = $this->getOrder()->getShippingTrackingDetails(true);
        if (!count($trackingDetails)) {
            return array();
        }

        $magentoOrder = $this->getOrder()->getMagentoOrder();

        // Filter exist tracks
        // ------------------------
        foreach ($magentoOrder->getTracksCollection() as $track) {

            foreach ($trackingDetails as $key => $trackingDetail) {
                if ($track->getData('number') != $trackingDetail['number']) {
                    continue;
                }

                if ($track->getData('carrier_code') == $this->getCarrierCodeByTitle($trackingDetail['title'])) {
                    unset($trackingDetails[$key]);
                }
            }

        }
        // ------------------------

        return $trackingDetails;
    }

    protected function getCarrierCodeByTitle($title)
    {
        if (!$title) {
            throw new Exception('Carrier title cannot be empty.');
        }

        $ebayCarriers = Mage::helper('M2ePro/Sales')->getEbayCarriers();
        $carrierCode = strtolower($title);

        return isset($ebayCarriers[$carrierCode]) ? $carrierCode : 'custom';
    }

    // ########################################

    protected function updateOrderStatus()
    {
        $magentoOrder = $this->getOrder()->getMagentoOrder();
        $magentoOrderStatus = $this->getOrder()->getAccount()->getOrderStatusOnShippingComplete();

        $magentoOrder->setStatus($magentoOrderStatus)
                     ->setIsCustomerNotified(false)
                     ->save();
    }

    // ########################################

    private function prepareObservers($skipTrack = false)
    {
        Mage::unregister('m2epro_skip_shipping_observer');
        Mage::register('m2epro_skip_shipping_observer', true);

        if ($skipTrack) {
            Mage::unregister('m2epro_skip_track_observer');
            Mage::register('m2epro_skip_track_observer', true);
        }
    }

    // ########################################
}