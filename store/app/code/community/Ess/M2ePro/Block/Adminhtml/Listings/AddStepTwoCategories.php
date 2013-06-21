<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_AddStepTwoCategories extends Mage_Adminhtml_Block_Widget_View_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingsAddStepTwoCategories');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listings_categories';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Add Listing [Select Categories]');
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
            'onclick'   => 'CategoriesTreeHandlersObj.back_click(\'' .$this->getUrl('*/*/add',array('step'=>'1')).'\')',
            'class'     => 'back'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CategoriesTreeHandlersObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('save_and_next', array(
            'label'     => Mage::helper('M2ePro')->__('Next'),
            'onclick'   => 'CategoriesTreeHandlersObj.save_click(\''.$this->getUrl('*/*/add',array('step'=>'2','remember_categories'=>'yes')).'\')',
            'class'     => 'next save_and_next_button'
        ));

        $this->_addButton('save_and_go_to_listings_list', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'CategoriesTreeHandlersObj.save_click(\'' . $this->getUrl('*/*/add',array('step'=>'2','save'=>'yes','back'=>'list')) .'\')',
            'class'     => 'save save_and_go_to_listings_list_button'
        ));

        $this->_addButton('save_and_go_to_listing_view', array(
            'label'     => Mage::helper('M2ePro')->__('Save And View Listing'),
            'onclick'   => 'CategoriesTreeHandlersObj.save_click(\'' . $this->getUrl('*/*/add',array('step'=>'2','save'=>'yes','back'=>'view')) .'\')',
            'class'     => 'save save_and_go_to_listing_view_button'
        ));
        //------------------------------
    }

    protected function _beforeToHtml()
    {
        $this->setChild('categories_tree', $this->getLayout()->createBlock('M2ePro/adminhtml_listings_categories_tree'));
        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        return parent::_toHtml().$this->getChildHtml('categories_tree');
    }
}