<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_Products extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingsProducts');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listings_products';
        //------------------------------

        // Set header text
        //------------------------------
        $listingData = Mage::registry('M2ePro_data');
        $headerText = Mage::helper('M2ePro')->__('Add Products To Listing "%title%" ');
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
                'onclick'   => 'ProductsGridHandlersObj.back_click(\''.Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listings/index').'\')',
                'class'     => 'back'
            ));
        }
        
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'ProductsGridHandlersObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'ProductsGridHandlersObj.save_click(\''.$this->getUrl('*/*/products',array('id' => $listingData['id'],'back' => Mage::helper('M2ePro')->getBackUrlParam('*/adminhtml_listings/index'))).'\')',
            'class'     => 'save'
        ));
        //------------------------------
    }

    public function _toHtml()
    {
        $listingData = Mage::registry('M2ePro_data');
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_products_help');
        $startHtmlDivGrid = '<div id="'.$this->getId().'Grid'.(isset($listingData['id'])?$listingData['id']:'').'">';
        return str_replace($startHtmlDivGrid,$helpBlock->toHtml().$startHtmlDivGrid,parent::_toHtml());
    }
}