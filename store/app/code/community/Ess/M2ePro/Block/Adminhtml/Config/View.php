<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Config_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('configView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_config_view';
        //------------------------------

        // Set header text
        //------------------------------
        if (Mage::registry('m2epro_config_mode') == 'ess') {
            $this->_headerText = Mage::helper('M2ePro')->__('View ESS Config Data');
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('View M2ePro Config Data');
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
        //------------------------------
    }

    public function getHeaderHtml()
    {
        return '<div style="display:none;" id="grid_container_flag"></div>
                <script type="text/javascript">
                    $(\'grid_container_flag\').parentNode.parentNode.parentNode.parentNode.parentNode.remove();
                </script>';
    }
}