<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Messages extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('messages');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_messages';
        $this->_mode = 'messages';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('My Messages');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('reset', array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'CommonHandlersObj.reset_click()',
            'class'   => 'reset'
        ));
        //------------------------------
    }

    public function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_messages_help');

        $filtersBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_accounts_switcher');
        $filtersBlock->setUseConfirm(false);

        $formBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_messages_form');
        $startHtmlDivGrid = '<div id="'.$this->getId().'Grid">';

        return str_replace($startHtmlDivGrid,$helpBlock->toHtml().$filtersBlock->toHtml().$formBlock->toHtml().$startHtmlDivGrid,parent::_toHtml());
    }
}