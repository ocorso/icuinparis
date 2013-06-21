<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_LogsCleaning extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('logsCleaning');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml';
        $this->_mode = 'logsCleaning';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Logs Clearing');
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
                'onclick'   => 'LogsCleaningHandlersObj.back_click(\''.Mage::helper('M2ePro')->getBackUrl('*/adminhtml_logs/index').'\')',
                'class'     => 'back'
            ));
        }
        
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'LogsCleaningHandlersObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('run_all_now', array(
            'label'     => Mage::helper('M2ePro')->__('Run Enabled Now'),
            'onclick'   => 'LogsCleaningHandlersObj.runNowLogs()',
            'class'     => 'save'
        ));
        
        $this->_addButton('clear_all_logs', array(
            'label'     => Mage::helper('M2ePro')->__('Clear All Logs'),
            'onclick'   => 'LogsCleaningHandlersObj.clearAllLogs()',
            'class'     => 'save'
        ));

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save Settings'),
            'onclick'   => 'LogsCleaningHandlersObj.saveCleaningSettings()',
            'class'     => 'save'
        ));
        //------------------------------
    }
}