<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_OrderItem extends Mage_Core_Model_Abstract
{
    protected $_order = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Orders_OrderItem');
    }

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
        return $this->_order;
    }

    // ########################################

    public function getItemsTotalPrice()
    {
        $connRead = $this->getResource()->getReadConnection();

        $dbSelect = $connRead->select()
                             ->from($this->getResource()->getTable('M2ePro/Orders_OrderItem'),new Zend_Db_Expr('SUM(`price`*`qty_purchased`)'))
                             ->where('`ebay_order_id` = ?',(int)$this->getOrder()->getId());

        return round($connRead->fetchOne($dbSelect), 2);
    }

    // ########################################

    public function getOptions($asArray = false)
    {
        if (is_null($this->getId())) {
            throw new Exception('Order item does not exist.');
        }

        $options = $this->getData('variations');

        if (is_null($options)) {
            return $asArray ? array() : $options;
        }

        return $asArray ? unserialize($options) : $options;
    }

    // ########################################
}