<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Synchronization extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('synchronization');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml';
        $this->_mode = 'synchronization';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Synchronization');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (!(Mage::getModel('M2ePro/Wizard')->isActive() && Mage::getModel('M2ePro/Wizard')->getStatus() == Ess_M2ePro_Model_Wizard::STATUS_SYNCHRONIZATION)) {

            $this->_addButton('goto_accounts', array(
                'label'     => Mage::helper('M2ePro')->__('Accounts'),
                'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_accounts/index').'\')',
                'class'     => 'button_link'
            ));

            $this->_addButton('view_log', array(
                'label'     => Mage::helper('M2ePro')->__('View Log'),
                'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_logs/synchronizations',array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_synchronization/index'))).'\')',
                'class'     => 'button_link'
            ));

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'SynchronizationHandlersObj.reset_click()',
                'class'     => 'reset'
            ));

            $this->_addButton('run_all_enabled_now', array(
                'label'     => Mage::helper('M2ePro')->__('Run Enabled Now'),
                'onclick'   => 'SynchronizationHandlersObj.saveSettings(\'runAllEnabledNow\');',
                'class'     => 'save'
            ));

        }

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save Settings'),
            'onclick'   => 'SynchronizationHandlersObj.saveSettings()',
            'class'     => 'save'
        ));

        if (Mage::getModel('M2ePro/Wizard')->isActive() && Mage::getModel('M2ePro/Wizard')->getStatus() == Ess_M2ePro_Model_Wizard::STATUS_SYNCHRONIZATION) {

            $this->_addButton('close', array(
                'label'     => Mage::helper('M2ePro')->__('Complete This Step'),
                'onclick'   => 'SynchronizationHandlersObj.completeStep();',
                'class'     => 'close'
            ));
        }
        //------------------------------
    }

    public function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        SynchProgressBarObj = new ProgressBar('synchronization_progress_bar');
        SynchWrapperObj = new AreaWrapper('synchronization_content_container');
        SynchWrapperObj.addDivClearBothToContainer();
    });

</script>
JAVASCRIPT;

        return $javascriptsMain.
               '<div id="synchronization_progress_bar"></div>'.
               '<div id="synchronization_content_container">'.
               parent::_toHtml().
               '</div>';
    }
}