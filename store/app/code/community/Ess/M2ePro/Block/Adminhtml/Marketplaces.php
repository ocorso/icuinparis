<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Marketplaces extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('marketplaces');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml';
        $this->_mode = 'marketplaces';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Marketplaces');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (Mage::getModel('M2ePro/Wizard')->isActive() &&
            Mage::getModel('M2ePro/Wizard')->getStatus() == Ess_M2ePro_Model_Wizard::STATUS_MARKETPLACES) {

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'CommonHandlersObj.reset_click()',
                'class'     => 'reset'
            ));
            
            $this->_addButton('close', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Complete This Step'),
                'onclick'   => 'MarketplacesHandlersObj.completeStep();',
                'class'     => 'close'
            ));

        } else {

            $this->_addButton('goto_listing_templates', array(
                'label'     => Mage::helper('M2ePro')->__('General Templates'),
                'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_listingTemplates/index").'\')',
                'class'     => 'button_link'
            ));

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'MarketplacesHandlersObj.reset_click()',
                'class'     => 'reset'
            ));

            $this->_addButton('run_synch_now', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Update'),
                'onclick'   => 'MarketplacesHandlersObj.saveSettings(\'runSynchNow\');',
                'class'     => 'save'
            ));

        }
        //------------------------------
    }

    public function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        MarketplacesProgressBarObj = new ProgressBar('marketplaces_progress_bar');
        MarketplacesWrapperObj = new AreaWrapper('marketplaces_content_container');
        MarketplacesWrapperObj.addDivClearBothToContainer();
    });

</script>
JAVASCRIPT;

        return $javascriptsMain.
               '<div id="marketplaces_progress_bar"></div>'.
               '<div id="marketplaces_content_container">'.
               parent::_toHtml().
               '</div>';
    }
}