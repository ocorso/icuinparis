<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_DescriptionTemplates_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('descriptionTemplatesEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_descriptionTemplates';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        if (Mage::registry('M2ePro_data') && Mage::registry('M2ePro_data')->getId()) {
            $this->_headerText = Mage::helper('M2ePro')->__('Edit Description Template');
            $this->_headerText .= ' "'.$this->htmlEscape(Mage::registry('M2ePro_data')->getTitle()).'"';
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Add Description Template');
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
            'onclick'   => 'DescriptionTemplatesHandlersObj.back_click(\'' .Mage::helper('M2ePro')->getBackUrl('list').'\')',
            'class'     => 'back'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'DescriptionTemplatesHandlersObj.reset_click()',
            'class'     => 'reset'
        ));

        if (Mage::registry('M2ePro_data') && Mage::registry('M2ePro_data')->getId()) {

            $this->_addButton('duplicate', array(
                'label'     => Mage::helper('M2ePro')->__('Duplicate'),
                'onclick'   => 'DescriptionTemplatesHandlersObj.duplicate_click(\'descriptionTemplates\')',
                'class'     => 'add M2ePro_duplicate_button'
            ));

            $this->_addButton('delete', array(
                'label'     => Mage::helper('M2ePro')->__('Delete'),
                'onclick'   => 'DescriptionTemplatesHandlersObj.delete_click()',
                'class'     => 'delete M2ePro_delete_button'
            ));
        }

        $this->_addButton('preview', array(
            'label'   => Mage::helper('adminhtml')->__('Preview'),
            'onclick' => 'DescriptionTemplatesHandlersObj.preview_click()',
            'class'   => 'bt_preview',
        ));

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'DescriptionTemplatesHandlersObj.save_click()',
            'class'     => 'save'
        ));

        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'DescriptionTemplatesHandlersObj.save_and_edit_click()',
            'class'     => 'save'
        ));
        //------------------------------
    }
}