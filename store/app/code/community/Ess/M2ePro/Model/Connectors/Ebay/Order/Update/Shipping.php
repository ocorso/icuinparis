<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_Order_Update_Shipping extends Ess_M2ePro_Model_Connectors_Ebay_Order_Abstract
{
    protected $carrierCode = NULL;
    protected $trackingNumber = NULL;

    // ########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Orders_Order $order, $action)
    {
        if (isset($params['carrier_code']) && isset($params['tracking_number'])) {
            $this->carrierCode = $params['carrier_code'];
            $this->trackingNumber = $params['tracking_number'];
        }

        parent::__construct($params, $order, $action);
    }

    // ########################################

    protected function getCommand()
    {
        return array('sales', 'update', 'status');
    }

    // ########################################

    protected function validateNeedRequestSend()
    {
        if (!parent::validateNeedRequestSend()) {
            return false;
        }

        if (!$this->order->isPaymentCompleted()) {
            // Parser hack -> Mage::helper('M2ePro')->__('Shipping status for eBay order cannot be updated. Reason: eBay order is not Paid.');
            $message = 'Shipping status for eBay order cannot be updated. Reason: eBay order is not Paid.';
            $this->order->addErrorLogMessage($message);

            return false;
        }

        if ($this->action == Ess_M2ePro_Model_Connectors_Ebay_Order_Dispatcher::ACTION_SHIP && $this->order->isShippingCompleted()) {
            // Parser hack -> Mage::helper('M2ePro')->__('Shipping status for eBay order cannot be updated. Reason: Shipping status is already Shipped.');
            $message = 'Shipping status for eBay order cannot be updated. Reason: Shipping status is already Shipped.';
            $this->order->addErrorLogMessage($message);

            return false;
        }

        if ($this->action == Ess_M2ePro_Model_Connectors_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK &&
            (!$this->carrierCode || !$this->trackingNumber)) {
            // Parser hack -> Mage::helper('M2ePro')->__('Shipping status for eBay order cannot be updated. Reason: Wrong tracking data.');
            $message = 'Shipping status for eBay order cannot be updated. Reason: Wrong tracking data.';
            $this->order->addErrorLogMessage($message);

            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        $requestData = parent::getRequestData();

        if ($this->action == Ess_M2ePro_Model_Connectors_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK) {
            $requestData['carrier_code'] = $this->carrierCode;
            $requestData['tracking_number'] = $this->trackingNumber;
        }

        return $requestData;
    }

    // ########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function prepareResponseData($response)
    {
        if ($this->resultType != parent::MESSAGE_TYPE_ERROR) {

            if (!isset($response['result']) || !$response['result']) {
                // Parser hack -> Mage::helper('M2ePro')->__('Shipping status for eBay order cannot be updated. Reason: eBay Failure.');
                $message = 'Shipping status for eBay order cannot be updated. Reason: eBay Failure.';
                $this->order->addErrorLogMessage($message);

                return false;
            }

            if (isset($this->trackingDetails['tracking_number']) && isset($this->trackingDetails['carrier_code'])) {
                // Parser hack -> Mage::helper('M2ePro')->__('Tracking number %num% for %code% has been sent to eBay.');
                $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Tracking number %num% for %code% has been sent to eBay.', array(
                    '!num'  => $this->trackingDetails['tracking_number'],
                    '!code' => $this->trackingDetails['carrier_code']
                ));
                $this->order->addSuccessLogMessage($message);
            }

            if (!$this->order->isShippingCompleted()) {
                $this->order->setData('shipping_status', Ess_M2ePro_Model_Orders_Order::SHIPPING_STATUS_COMPLETED)
                            ->save();

                // Parser hack -> Mage::helper('M2ePro')->__('Shipping status for eBay order was updated to Shipped.');
                $message = 'Shipping status for eBay order was updated to Shipped.';
                $this->order->addSuccessLogMessage($message);
            }

        }

        return $response;
    }

    // ########################################
}