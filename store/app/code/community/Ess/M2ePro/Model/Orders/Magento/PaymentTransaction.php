<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Magento_PaymentTransaction
{
    protected $_order = NULL;

    // ########################################

    public function setOrder(Ess_M2ePro_Model_Orders_Order $order)
    {
        if (is_null($order->getId())) {
            throw new Exception('Order does not exist.');
        }

        $this->_order = $order;

        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Orders_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            throw new Exception('Order was not set.');
        }

        return $this->_order;
    }

    // ########################################

    public function createPaymentTransaction()
    {
        try {

            $externalTransactions = $this->getOrder()->getExternalTransactionsCollection();
            if (!$externalTransactions->getSize()) {
                return false;
            }

            $magentoOrder = $this->getOrder()->getMagentoOrder();
            if (!$magentoOrder) {
                return false;
            }

            $payment = $magentoOrder->getPayment();

            foreach ($externalTransactions as $externalTransaction) {
                $transactionId = $externalTransaction->getData('ebay_id');
                $existTransaction = $payment->getTransaction($transactionId);

                if ($existTransaction && $existTransaction instanceof Mage_Sales_Model_Order_Payment_Transaction) {
                    continue;
                }

                $payment->setTransactionId($transactionId);
                $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);

                if (defined('Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS')) {
                    $info = array(
                        'Fee'  => $externalTransaction->getData('fee'),
                        'Sum'  => $externalTransaction->getData('sum'),
                        'Time' => $externalTransaction->getData('time')
                    );
                    $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $info);
                }

                $transaction->save();

                return true;
            }

        } catch (Exception $e) {
            // Parser hack -> Mage::helper('M2ePro')->__('Payment transaction was not created. Reason: %msg%.');
            $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Payment transaction was not created. Reason: %msg%.', array('msg' => $e->getMessage()));
            $this->getOrder()->addErrorLogMessage($message, $e->getTraceAsString());
        }

        return false;
    }

    // ########################################
}