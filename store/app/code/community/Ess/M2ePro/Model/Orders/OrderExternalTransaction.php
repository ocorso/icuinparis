<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_OrderExternalTransaction extends Mage_Core_Model_Abstract
{
    protected $_order = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Orders_OrderExternalTransaction');
    }

    // ########################################

    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('M2ePro/Orders_Order')->load($this->getData('order_id'));
        }
        return $this->_order;
    }

    // ########################################
}