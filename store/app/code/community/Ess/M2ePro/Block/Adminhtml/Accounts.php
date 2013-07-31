<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Accounts extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('accounts');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_accounts';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('eBay Accounts');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('goto_feedbacks', array(
            'label'     => Mage::helper('M2ePro')->__('Feedbacks'),
            'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_feedbacks/index").'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_listing_templates', array(
            'label'     => Mage::helper('M2ePro')->__('General Templates'),
            'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_listingTemplates/index").'\')',
            'class'     => 'button_link'
        ));
        
        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add eBay Account'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/*/new').'\')',
            'class'     => 'add'
        ));
        //------------------------------
    }
}