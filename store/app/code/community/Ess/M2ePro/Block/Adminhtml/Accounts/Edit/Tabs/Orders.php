<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Accounts_Edit_Tabs_Orders extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('accountsTabsOrders');
        //------------------------------

        $this->setTemplate('M2ePro/accounts/tabs/orders.phtml');
    }

    protected function _beforeToHtml()
    {
        $data = Mage::registry('M2ePro_data');

        //----------------------------
        $temp = Mage::getModel('core/website')->getCollection()->setOrder('sort_order','ASC')->toArray();
        $this->websites = $temp['items'];
        //----------------------------

        //----------------------------
        $temp = Mage::getModel('customer/group')->getCollection()->toArray();
        $this->groups = $temp['items'];
        //----------------------------

        //----------------------------
        $selectedStore = isset($data['orders_listings_store_id']) && !is_null($data['orders_listings_store_id']) ? $data['orders_listings_store_id'] : '';
        $blockStoreSwitcher = $this->getLayout()->createBlock('M2ePro/adminhtml_storeSwitcher', '', array('id'=>'orders_listings_store_id','selected' => $selectedStore));
        $this->setChild('orders_listings_store_id', $blockStoreSwitcher);
        //----------------------------

        //----------------------------
        $selectedStore = isset($data['orders_ebay_store_id']) && !is_null($data['orders_ebay_store_id']) ? $data['orders_ebay_store_id'] : '';
        $blockStoreSwitcher = $this->getLayout()->createBlock('M2ePro/adminhtml_storeSwitcher', '', array('id'=>'orders_ebay_store_id','selected' => $selectedStore));
        $this->setChild('orders_ebay_store_id', $blockStoreSwitcher);
        //----------------------------

        return parent::_beforeToHtml();
    }

    protected $_possibleMagentoStatuses = null;

    public function getMagentoOrderStatusList()
    {
        if (is_null($this->_possibleMagentoStatuses)) {
            $this->_possibleMagentoStatuses = Mage::getSingleton('sales/order_config')->getStatuses();
        }

        return $this->_possibleMagentoStatuses;
    }
}