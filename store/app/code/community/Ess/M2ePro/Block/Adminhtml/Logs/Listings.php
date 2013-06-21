<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */
 
class Ess_M2ePro_Block_Adminhtml_Logs_Listings extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('logsListings');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_logs_listings';
        //------------------------------

        // Set header text
        //------------------------------
        $listingData = Mage::registry('M2ePro_data');

        if (isset($listingData['id'])) {
            $this->_headerText = Mage::helper('M2ePro')->__('Log For Listing');
            $this->_headerText .= ' "'.$this->htmlEscape($listingData['title']).'"';
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Listings Log');
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

        if (!is_null($this->getRequest()->getParam('back'))) {

            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlersObj.back_click(\''.Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listings/index').'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_listings/index").'\')',
            'class'     => 'button_link'
        ));

        if (isset($listingData['id'])) {

            $this->_addButton('goto_listing_settings', array(
                'label'     => Mage::helper('M2ePro')->__('Listing Settings'),
                'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_listings/edit", array('id' => $listingData['id'])).'\')',
                'class'     => 'button_link'
            ));

            $this->_addButton('goto_listing_items', array(
                'label'     => Mage::helper('M2ePro')->__('Listing Items'),
                'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_listings/view", array('id' => $listingData['id'])).'\')',
                'class'     => 'button_link'
            ));
        }

        $this->_addButton('goto_logs_cleaning', array(
            'label'     => Mage::helper('M2ePro')->__('Clearing'),
            'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_logsCleaning/index",array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_logs/listings'))).'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlersObj.reset_click()',
            'class'     => 'reset'
        ));

        if (isset($listingData['id'])) {
            
            $this->_addButton('show_general_log', array(
                'label'     => Mage::helper('M2ePro')->__('Show General Log'),
                'onclick'   => 'setLocation(\'' .$this->getUrl("*/*/*").'\')',
                'class'     => 'show_general_log'
            ));
        }
        //------------------------------
    }

    public function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_logs_listings_help');
        $startHtmlDivGrid = '<div id="'.$this->getId().'Grid">';
        return str_replace($startHtmlDivGrid,$helpBlock->toHtml().$startHtmlDivGrid,parent::_toHtml());
    }
}