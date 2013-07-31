<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_ListingTemplates extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingTemplates');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listingTemplates';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('General Templates');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_listings/index").'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_accounts', array(
            'label'     => Mage::helper('M2ePro')->__('Accounts'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_accounts/index').'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_marketplaces', array(
            'label'     => Mage::helper('M2ePro')->__('Marketplaces'),
            'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_marketplaces/index").'\')',
            'class'     => 'button_link'
        ));
        
        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add General Template'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/*/new').'\')',
            'class'     => 'add'
        ));
        //------------------------------
    }
}