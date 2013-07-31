<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_Order_Dispatcher extends Mage_Core_Model_Abstract
{
    const ACTION_PAY        = 2;
    const ACTION_SHIP       = 1;
    const ACTION_SHIP_TRACK = 3;

    // ########################################

    public function process($action, $orders, array $params = array())
    {
        $orders = $this->prepareOrders($orders);

        switch ($action) {
            case self::ACTION_PAY:
                $result = $this->processOrders($orders, $action, 'Ess_M2ePro_Model_Connectors_Ebay_Order_Update_Payment', $params);
                break;

            case self::ACTION_SHIP:
            case self::ACTION_SHIP_TRACK:
                $result = $this->processOrders($orders, $action, 'Ess_M2ePro_Model_Connectors_Ebay_Order_Update_Shipping', $params);
                break;

            default;
                $result = false;
                break;
        }

        return $result;
    }

    // ########################################

    protected function processOrders(array $orders, $action, $connectorNameSingle, array $params = array())
    {
        if (!count($orders)) {
            return false;
        }

        foreach ($orders as $order) {

            try {
                $connector = new $connectorNameSingle($params, $order, $action);
                $connector->process();
            } catch (Exception $e) {
                // Parser hack -> Mage::helper('M2ePro')->__('eBay Order status was not updated. Reason: %msg%.');
                $message = Mage::getModel('M2ePro/LogsBase')->encodeDescription('eBay Order status was not updated. Reason: %msg%.',array('msg'=>$e->getMessage()));
                $order->addErrorLogMessage($message, $e->getTraceAsString());

                return false;
            }

        }

        return true;
    }

    // ########################################

    protected function prepareOrders($orders)
    {
        !is_array($orders) && $orders = array($orders);
        $preparedOrders = array();

        foreach ($orders as $order) {
            if ($order instanceof Ess_M2ePro_Model_Orders_Order) {
                $preparedOrders[] = $order;
            } else if (is_numeric($order)) {
                $preparedOrders[] = Mage::getModel('M2ePro/Orders_Order')->load((int)$order);
            }
        }

        return $preparedOrders;
    }

    // ########################################
}