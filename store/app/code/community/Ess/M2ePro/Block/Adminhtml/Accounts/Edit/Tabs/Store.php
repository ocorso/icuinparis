<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Accounts_Edit_Tabs_Store extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('accountsTabsStore');
        //------------------------------

        $this->setTemplate('M2ePro/accounts/tabs/store.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh'),
                                'onclick' => 'AccountsHandlersObj.ebayStoreUpdate();',
                                'class' => 'update_ebay_store'
                            ) );
        $this->setChild('update_ebay_store',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Hide'),
                                'onclick' => 'AccountsHandlersObj.ebayStoreSelectCategoryHide();',
                                'class' => 'hide_selected_category'
                            ) );
        $this->setChild('hide_selected_category',$buttonBlock);
        //-------------------------------
        
        return parent::_beforeToHtml();
    }
}