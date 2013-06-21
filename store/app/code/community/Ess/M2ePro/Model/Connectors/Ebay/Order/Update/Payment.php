<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_Order_Update_Payment extends Ess_M2ePro_Model_Connectors_Ebay_Order_Abstract
{
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

        if ($this->order->isPaymentCompleted()) {
            // Parser hack -> Mage::helper('M2ePro')->__('Payment status for eBay order cannot be updated. Reason: Payment status is already Paid.');
            $message = 'Payment status for eBay order cannot be updated. Reason: Payment status is already Paid.';
            $this->order->addErrorLogMessage($message);

            return false;
        }

        return true;
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
                // Parser hack -> Mage::helper('M2ePro')->__('Payment status for eBay order cannot be updated. Reason: eBay Failure.');
                $message = 'Payment status for eBay order cannot be updated. Reason: eBay Failure.';
                $this->order->addErrorLogMessage($message);
                return false;
            }

            $this->order->setData('payment_status_m2e_code', Ess_M2ePro_Model_Orders_Order::PAYMENT_STATUS_COMPLETED)
                        ->save();

            // Parser hack -> Mage::helper('M2ePro')->__('Payment status for eBay order was updated to Paid.');
            $message = 'Payment status for eBay order was updated to Paid.';
            $this->order->addSuccessLogMessage($message);

        }

        return $response;
    }

    // ########################################
}