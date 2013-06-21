<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_ListingTemplates_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingTemplatesTabs');
        //------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('tab_general', array(
                'label'   => Mage::helper('M2ePro')->__('General'),
                'title'   => Mage::helper('M2ePro')->__('General'),
                'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_listingTemplates_edit_tabs_general')->toHtml(),
            ))
            ->addTab('tab_specifics', array(
                'label'   => Mage::helper('M2ePro')->__('Item Specifics'),
                'title'   => Mage::helper('M2ePro')->__('Item Specifics'),
                'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_listingTemplates_edit_tabs_specifics')->toHtml(),
            ))
            ->addTab('tab_upgrades', array(
                'label'   => Mage::helper('M2ePro')->__('Listing Upgrades'),
                'title'   => Mage::helper('M2ePro')->__('Listing Upgrades'),
                'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_listingTemplates_edit_tabs_upgrades')->toHtml(),
            ))
            ->addTab('tab_shipping', array(
                'label'   => Mage::helper('M2ePro')->__('Shipping'),
                'title'   => Mage::helper('M2ePro')->__('Shipping'),
                'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_listingTemplates_edit_tabs_shipping')->toHtml(),
            ))
            ->addTab('tab_payment', array(
                'label'   => Mage::helper('M2ePro')->__('Payment'),
                'title'   => Mage::helper('M2ePro')->__('Payment'),
                'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_listingTemplates_edit_tabs_payment')->toHtml(),
            ))
            ->addTab('tab_refund', array(
                'label'   => Mage::helper('M2ePro')->__('Return Policy'),
                'title'   => Mage::helper('M2ePro')->__('Return Policy'),
                'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_listingTemplates_edit_tabs_refund')->toHtml(),
            ))
            ->setActiveTab($this->getRequest()->getParam('tab', 'tab_general'));

        return parent::_beforeToHtml();
    }
}