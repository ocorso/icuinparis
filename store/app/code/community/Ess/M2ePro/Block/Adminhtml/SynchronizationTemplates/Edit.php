<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_SynchronizationTemplates_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('synchronizationTemplatesEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_synchronizationTemplates';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        if (Mage::registry('M2ePro_data') && Mage::registry('M2ePro_data')->getId()) {
            $this->_headerText = Mage::helper('M2ePro')->__('Edit Synchronization Template');
            $this->_headerText .= ' "'.$this->htmlEscape(Mage::registry('M2ePro_data')->getTitle()).'"';
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Add Synchronization Template');
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

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'SynchronizationTemplatesHandlersObj.back_click(\'' .Mage::helper('M2ePro')->getBackUrl('list').'\')',
            'class'     => 'back'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'SynchronizationTemplatesHandlersObj.reset_click()',
            'class'     => 'reset'
        ));

        if (Mage::registry('M2ePro_data') && Mage::registry('M2ePro_data')->getId()) {

            $this->_addButton('duplicate', array(
                'label'     => Mage::helper('M2ePro')->__('Duplicate'),
                'onclick'   => 'SynchronizationTemplatesHandlersObj.duplicate_click(\'synchronizationTemplates\')',
                'class'     => 'add M2ePro_duplicate_button'
            ));

            $this->_addButton('delete', array(
                'label'     => Mage::helper('M2ePro')->__('Delete'),
                'onclick'   => 'SynchronizationTemplatesHandlersObj.delete_click()',
                'class'     => 'delete M2ePro_delete_button'
            ));
        }

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'SynchronizationTemplatesHandlersObj.save_click()',
            'class'     => 'save'
        ));

        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'SynchronizationTemplatesHandlersObj.save_and_edit_click(\'\',\'synchronizationTemplatesTabs\')',
            'class'     => 'save'
        ));
        //------------------------------
    }
}