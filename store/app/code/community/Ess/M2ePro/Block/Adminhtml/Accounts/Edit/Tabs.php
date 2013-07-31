<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Accounts_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('accountsTabs');
        //------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('tab_general', array(
            'label'   => Mage::helper('M2ePro')->__('General'),
            'title'   => Mage::helper('M2ePro')->__('General'),
            'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_accounts_edit_tabs_general')->toHtml(),
         ));
            
        if (Mage::registry('M2ePro_data') && Mage::registry('M2ePro_data')->getId()) {
            
            $this->addTab('tab_store', array(
                    'label'   => Mage::helper('M2ePro')->__('eBay Store'),
                    'title'   => Mage::helper('M2ePro')->__('eBay Store'),
                    'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_accounts_edit_tabs_store')->toHtml(),
                ))
                ->addTab('tab_orders', array(
                    'label'   => Mage::helper('M2ePro')->__('Orders'),
                    'title'   => Mage::helper('M2ePro')->__('Orders'),
                    'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_accounts_edit_tabs_orders')->toHtml(),
                ))
                ->addTab('tab_feedbacks', array(
                    'label'   => Mage::helper('M2ePro')->__('Feedbacks'),
                    'title'   => Mage::helper('M2ePro')->__('Feedbacks'),
                    'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_accounts_edit_tabs_feedbacks')->toHtml(),
                ));
        }

        $this->setActiveTab($this->getRequest()->getParam('tab', 'tab_general'));

        return parent::_beforeToHtml();
    }
}