<?php

class Icu_Videos_Block_Adminhtml_Videos_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'videos';
        $this->_controller = 'adminhtml_videos';
        
        $this->_updateButton('save', 'label', Mage::helper('videos')->__('Save Video'));
        $this->_updateButton('delete', 'label', Mage::helper('videos')->__('Delete Video'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('videos_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'videos_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'videos_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('videos_data') && Mage::registry('videos_data')->getId() ) {
            return Mage::helper('videos')->__("Edit Video '%s'", $this->htmlEscape(Mage::registry('videos_data')->getTitle()));
        } else {
            return Mage::helper('videos')->__('Add Video');
        }
    }
}