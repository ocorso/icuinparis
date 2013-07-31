<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Accounts_Edit_Tabs_Feedbacks extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('accountsTabsFeedbacks');
        //------------------------------

        $this->setTemplate('M2ePro/accounts/tabs/feedbacks.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Add Template'),
                                'onclick' => 'AccountsHandlersObj.feedbacksOpenAddForm();',
                                'class' => 'open_add_form'
                            ) );
        $this->setChild('open_add_form',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Cancel'),
                                'onclick' => 'AccountsHandlersObj.feedbacksCancelForm();',
                                'class' => 'cancel_form'
                            ) );
        $this->setChild('cancel_form',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Save'),
                                'onclick' => 'AccountsHandlersObj.feedbacksAddAction();',
                                'class' => 'add_action'
                            ) );
        $this->setChild('add_action',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Save'),
                                'onclick' => 'AccountsHandlersObj.feedbacksEditAction();',
                                'class' => 'edit_action'
                            ) );
        $this->setChild('edit_action',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $this->setChild('feedbacks_templates_grid', $this->getLayout()->createBlock('M2ePro/adminhtml_accounts_edit_tabs_feedbacks_grid'));
        //-------------------------------
        
        return parent::_beforeToHtml();
    }
}