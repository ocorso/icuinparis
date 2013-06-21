<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_EbayOrders extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('M2ePro/EbayOrders', 'id');
    }

    public function getItemsTotal(Ess_M2ePro_Model_EbayOrders $item)
    {
        $dbSelect = $this->getReadConnection()->select()
                                              ->from($this->getTable('M2ePro/EbayOrdersItems'),new Zend_Db_Expr('SUM(`price`*`qty_purchased`)'))
                                              ->where('`ebay_order_id` = ?',(int)$item->getId());

        return $this->getReadConnection()->fetchOne($dbSelect);
    }
}