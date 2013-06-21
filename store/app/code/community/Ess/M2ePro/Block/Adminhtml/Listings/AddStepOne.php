<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_AddStepOne extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        // Initialization block
        //------------------------------
        $this->setId('listingsAddStepOne');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listings';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Add Listing [Settings]');
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
            'onclick'   => 'ListingEditHandlersObj.back_click(\'' .$this->getUrl('*/*/index').'\')',
            'class'     => 'back'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'ListingEditHandlersObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('save_and_next', array(
            'label'     => Mage::helper('M2ePro')->__('Next'),
            'onclick'   => 'ListingEditHandlersObj.save_click(\''.$this->getUrl('*/*/add',array('step'=>'1')).'\')',
            'class'     => 'next'
        ));
        //------------------------------
    }
}