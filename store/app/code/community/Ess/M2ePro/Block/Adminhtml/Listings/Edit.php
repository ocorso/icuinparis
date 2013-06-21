<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingsEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listings';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        $listingData = Mage::registry('M2ePro_data');
        $headerText = Mage::helper('M2ePro')->__('Edit "%title%" Listing [Settings]');
        $this->_headerText = str_replace('%title%', $this->htmlEscape($listingData['title']), $headerText);
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (!is_null($this->getRequest()->getParam('back'))) {

            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'ListingEditHandlersObj.back_click(\''.Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listings/index').'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'ListingEditHandlersObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'ListingEditHandlersObj.save_click(\''.$this->getUrl('*/*/edit',array('id' => $listingData['id'],'back' => Mage::helper('M2ePro')->getBackUrlParam('list'))).'\')',
            'class'     => 'save'
        ));

        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'ListingEditHandlersObj.save_and_edit_click()',
            'class'     => 'save'
        ));
        //------------------------------
    }
}