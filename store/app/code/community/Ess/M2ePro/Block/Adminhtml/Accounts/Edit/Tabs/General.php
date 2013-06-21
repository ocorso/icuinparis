<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Accounts_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('accountsTabsGeneral');
        //------------------------------

        $this->setTemplate('M2ePro/accounts/tabs/general.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Get Token'),
                                'onclick' => 'AccountsHandlersObj.get_token();',
                                'class' => 'get_token_button'
                            ) );
        $this->setChild('get_token_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}