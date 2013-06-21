<?php

class Icu_Fdesigners_Block_Adminhtml_Fdesigners_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'fdesigners';
        $this->_controller = 'adminhtml_fdesigners';
        
        $this->_updateButton('save', 'label', Mage::helper('fdesigners')->__('Save Designer'));
        $this->_updateButton('delete', 'label', Mage::helper('fdesigners')->__('Delete Designer'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('fdesigners_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'fdesigners_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'fdesigners_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('fdesigners_data') && Mage::registry('fdesigners_data')->getId() ) {
            return Mage::helper('fdesigners')->__("Edit Designer '%s'", $this->htmlEscape(Mage::registry('fdesigners_data')->getTitle()));
        } else {
            return Mage::helper('fdesigners')->__('Add Designer');
        }
    }
}