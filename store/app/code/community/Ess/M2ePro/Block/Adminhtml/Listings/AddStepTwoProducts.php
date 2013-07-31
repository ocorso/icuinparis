<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_AddStepTwoProducts extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingsAddStepTwoProducts');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listings_products';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Add Listing [Select Products]');
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
            'onclick'   => 'ProductsGridHandlersObj.back_click(\'' .$this->getUrl('*/*/add',array('step'=>'1')).'\')',
            'class'     => 'back'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'ProductsGridHandlersObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('save_and_go_to_listings_list', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'ProductsGridHandlersObj.save_click(\'' . $this->getUrl('*/*/add',array('step'=>'2','save'=>'yes','back'=>'list')) .'\')',
            'class'     => 'save'
        ));

        $this->_addButton('save_and_go_to_listing_view', array(
            'label'     => Mage::helper('M2ePro')->__('Save And View Listing'),
            'onclick'   => 'ProductsGridHandlersObj.save_click(\'' . $this->getUrl('*/*/add',array('step'=>'2','save'=>'yes','back'=>'view')) .'\')',
            'class'     => 'save'
        ));
        //------------------------------
    }

    public function _toHtml()
    {
        $listingData = Mage::registry('M2ePro_data');
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_products_help');
        $startHtmlDivGrid = '<div id="listingsProductsGrid'.(isset($listingData['id'])?$listingData['id']:'').'">';
        return str_replace($startHtmlDivGrid,$helpBlock->toHtml().$startHtmlDivGrid,parent::_toHtml());
    }
}