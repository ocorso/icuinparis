<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connectors_Ebay_Order_Abstract extends Ess_M2ePro_Model_Connectors_Ebay_Abstract
{
    // ########################################

    /**
     * @var Ess_M2ePro_Model_EbayListings
     */
    protected $order = NULL;
    protected $action = NULL;

    // ########################################
    
    public function __construct(array $params = array(), Ess_M2ePro_Model_Orders_Order $order, $action = NULL)
    {
        $this->order = $order;
        $this->action = $action;

        parent::__construct();
    }

    // ########################################

    public function process()
    {
        if (!$this->validateNeedRequestSend()) {
            return false;
        }

        $result = parent::process();

        foreach ($this->messages as $message) {
            if ($message[parent::MESSAGE_TYPE_KEY] != parent::MESSAGE_TYPE_ERROR) {
                continue;
            }

            // Parser hack -> Mage::helper('M2ePro')->__('eBay Order status was not updated. Reason: %msg%');
            $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('eBay Order status was not updated. Reason: %msg%', array('msg' => $message[parent::MESSAGE_TEXT_KEY]));
            $this->order->addErrorLogMessage($message);
        }

        return $result;
    }

    //----------------------------------------

    protected function validateNeedRequestSend()
    {
        if (!in_array($this->action, array(Ess_M2ePro_Model_Connectors_Ebay_Order_Dispatcher::ACTION_PAY,
                                           Ess_M2ePro_Model_Connectors_Ebay_Order_Dispatcher::ACTION_SHIP,
                                           Ess_M2ePro_Model_Connectors_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK))) {
            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        $requestData = array();
        $requestData['account'] = $this->order->getAccount()->getServerHash();
        $requestData['action'] = $this->action;

        if ($this->order->isCombined()) {
            $requestData['order_id'] = $this->order->getData('ebay_order_id');
        } else {
            $orderId = $this->order->getData('ebay_order_id');
            $orderIdParts = explode('-', $orderId);

            $requestData['item_id'] = $orderIdParts[0];
            $requestData['transaction_id'] = $orderIdParts[1];
        }

        return $requestData;
    }

    // ########################################
}