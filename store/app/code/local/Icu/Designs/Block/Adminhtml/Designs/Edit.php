<?php

class Icu_Designs_Block_Adminhtml_Designs_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'designs';
        $this->_controller = 'adminhtml_designs';
        
        $this->_updateButton('save', 'label', Mage::helper('designs')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('designs')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('designs_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'designs_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'designs_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('designs_data') && Mage::registry('designs_data')->getId() ) {
            return Mage::helper('designs')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('designs_data')->getTitle()));
        } else {
            return Mage::helper('designs')->__('Add Item');
        }
    }
}