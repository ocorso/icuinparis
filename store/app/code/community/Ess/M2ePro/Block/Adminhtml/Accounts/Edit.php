<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Accounts_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('accountsEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_accounts';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        if (Mage::getModel('M2ePro/Migration_Dispatcher')->isUserInterfaceActiveNow()) {

            if (Mage::registry('migration_account') !== false) {
                $temp = Mage::registry('migration_account');
                $this->_headerText = Mage::helper('M2ePro')->__('Migration For Account');
                $this->_headerText .= ' "'.$this->htmlEscape($temp['account']).'"';
            } else {
                $this->_headerText = Mage::helper('M2ePro')->__('Migration For Accounts Is Completed!');
            }

        } else {

            if (Mage::registry('M2ePro_data') && Mage::registry('M2ePro_data')->getId()) {
                $this->_headerText = Mage::helper('M2ePro')->__('Edit eBay Account');
                $this->_headerText .= ' "'.$this->htmlEscape(Mage::registry('M2ePro_data')->getTitle()).'"';
            } else {
                $this->_headerText = Mage::helper('M2ePro')->__('Add eBay Account');
            }

        }
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (Mage::getModel('M2ePro/Migration_Dispatcher')->isUserInterfaceActiveNow()) {
            
            if (Mage::registry('migration_account') !== false) {

                $this->_addButton('reset', array(
                    'label'     => Mage::helper('M2ePro')->__('Refresh'),
                    'onclick'   => 'AccountsHandlersObj.reset_click()',
                    'class'     => 'reset'
                ));
                
                $this->_addButton('save_and_continue', array(
                    'label'     => Mage::helper('M2ePro')->__('Save'),
                    'onclick'   => 'AccountsHandlersObj.save_and_edit_click(\'\',\'accountsTabs\')',
                    'class'     => 'save'
                ));

            } else {

                $this->_addButton('close', array(
                    'label'     => Mage::helper('M2ePro')->__('Complete This Step'),
                    'onclick'   => 'AccountsHandlersObj.completeStep();',
                    'class'     => 'close'
                ));

            }

        } else if (Mage::getModel('M2ePro/Wizard')->isActive() &&
                   Mage::getModel('M2ePro/Wizard')->getStatus() == Ess_M2ePro_Model_Wizard::STATUS_ACCOUNTS) {

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'AccountsHandlersObj.reset_click()',
                'class'     => 'reset'
            ));

            $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick'   => 'AccountsHandlersObj.save_and_edit_click(\'\',\'accountsTabs\')',
                'class'     => 'save'
            ));

            if ($this->getRequest()->getParam('id')) {
                
                $this->_addButton('add_new_account', array(
                    'label'     => Mage::helper('M2ePro')->__('Add New Account'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_accounts/new').'\')',
                    'class'     => 'add_new_account'
                ));

                $this->_addButton('close', array(
                    'label'     => Mage::helper('M2ePro')->__('Complete This Step'),
                    'onclick'   => 'AccountsHandlersObj.completeStep();',
                    'class'     => 'close'
                ));
            }

        } else {

            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'AccountsHandlersObj.back_click(\'' .Mage::helper('M2ePro')->getBackUrl('list').'\')',
                'class'     => 'back'
            ));

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'AccountsHandlersObj.reset_click()',
                'class'     => 'reset'
            ));

            if (Mage::registry('M2ePro_data') && Mage::registry('M2ePro_data')->getId()) {

                $this->_addButton('delete', array(
                    'label'     => Mage::helper('M2ePro')->__('Delete'),
                    'onclick'   => 'AccountsHandlersObj.delete_click()',
                    'class'     => 'delete M2ePro_delete_button'
                 ));

                $this->_addButton('save', array(
                    'label'     => Mage::helper('M2ePro')->__('Save'),
                    'onclick'   => 'AccountsHandlersObj.save_click()',
                    'class'     => 'save'
                ));
            }

            $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick'   => 'AccountsHandlersObj.save_and_edit_click(\'\',\'accountsTabs\')',
                'class'     => 'save'
            ));
        }
        //------------------------------
    }
}