<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Ebay_OrderExternalTransaction extends Mage_Core_Model_Abstract
{
    protected $_data = array();

    protected $_ebayOrder = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Orders_Ebay_OrderExternalTransaction');
    }

    // ########################################

    public function setEbayOrder(Ess_M2ePro_Model_Orders_Ebay_Order $order)
    {
        $this->_ebayOrder = $order;

        return $this;
    }

    /**
     * @throws Exception
     * @return Ess_M2ePro_Model_Orders_Ebay_Order
     */
    public function getEbayOrder()
    {
        if (is_null($this->_ebayOrder)) {
            throw new Exception('eBay order is not defined.');
        }

        return $this->_ebayOrder;
    }

    // ########################################

    public function initialize(array $data)
    {
        // Init general data
        // ------------------
        $this->_data['ebay_id'] = $data['ebay_id'];
        $this->_data['time'] = $data['time'];
        $this->_data['fee'] = (float)$data['fee'];
        $this->_data['sum'] = (float)$data['sum'];
        $this->_data['is_refund'] = (int)$data['is_refund']; // convert on server to binary
        // ------------------
    }

    // ########################################

    public function process()
    {
        if (!$this->getEbayOrder()->isNew()) {
            return;
        }

        // ------------------
        $this->_data['order_id'] = $this->getEbayOrder()->getOrder()->getId();
        // ------------------

        // Create order external transaction
        // ------------------
        $this->createOrderExternalTransaction();
        // ------------------
    }

    // ########################################

    protected function createOrderExternalTransaction()
    {
        $orderExternalTransactionModel = Mage::getModel('M2ePro/Orders_OrderExternalTransaction');

        $existTransaction = $orderExternalTransactionModel->getCollection()
                                                          ->addFieldToFilter('order_id', $this->getData('order_id'))
                                                          ->addFieldToFilter('ebay_id', $this->getData('ebay_id'))
                                                          ->getFirstItem();

        $existTransactionId = $existTransaction->getId();

        !$existTransactionId && $orderExternalTransactionModel->setData($this->getData());
        $existTransactionId && $orderExternalTransactionModel->load($existTransactionId)->addData($this->getData());

        $orderExternalTransactionModel->save();

        return $orderExternalTransactionModel;
    }

    // ########################################
}