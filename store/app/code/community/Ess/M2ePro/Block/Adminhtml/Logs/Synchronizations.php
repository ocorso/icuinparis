<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */
 
class Ess_M2ePro_Block_Adminhtml_Logs_Synchronizations extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('logsSynchronizations');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_logs_synchronizations';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Synchronization Log');
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
                'onclick'   => 'CommonHandlersObj.back_click(\''.Mage::helper('M2ePro')->getBackUrl('*/adminhtml_synchronization/index').'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('goto_synchronization', array(
            'label'     => Mage::helper('M2ePro')->__('Synchronization Settings'),
            'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_synchronization/index").'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_logs_cleaning', array(
            'label'     => Mage::helper('M2ePro')->__('Clearing'),
            'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_logsCleaning/index",array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_logs/synchronizations'))).'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlersObj.reset_click()',
            'class'     => 'reset'
        ));

        if (!is_null($this->getRequest()->getParam('synch_task'))) {

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
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_logs_synchronizations_help');
        $startHtmlDivGrid = '<div id="'.$this->getId().'Grid">';
        return str_replace($startHtmlDivGrid,$helpBlock->toHtml().$startHtmlDivGrid,parent::_toHtml());
    }
}