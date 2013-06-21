<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_SellingFormatTemplates extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('sellingFormatTemplates');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_sellingFormatTemplates';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Selling Format Templates');
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

        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Selling Format Template'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/*/new').'\')',
            'class'     => 'add'
        ));
        //------------------------------
    }
}