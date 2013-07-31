<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Welcome_Requirements extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardWelcomeRequirements');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/welcome/requirements.phtml');
    }

    protected function _beforeToHtml()
    {
        $serverPhpData = Mage::helper('M2ePro/Server')->getPhpSettings();

        //------------------------------
        $compareTo = '1.4.0.0';
        Mage::helper('M2ePro/Magento')->isMagentoGoMode() && $compareTo = '1.9.0.0';
        Mage::helper('M2ePro/Magento')->isMagentoProfessionalEdition() && $compareTo = '1.7.0.0';
        Mage::helper('M2ePro/Magento')->isMagentoEnterpriseEdition() && $compareTo = '1.7.0.0';

        $this->magento_version_value = Mage::helper('M2ePro/Magento')->getVersion(false);
        $this->magento_version_validation = version_compare($this->magento_version_value, $compareTo, '>=');
        //------------------------------

        //------------------------------
        $this->memory_limit_value = (int)$serverPhpData['memory_limit'];
        $this->memory_limit_validation = $this->memory_limit_value >= 256;
        //------------------------------

        //------------------------------
        $max_execution_time_value = (int)$serverPhpData['max_execution_time'];
        if ($max_execution_time_value <= 0) {
            $this->max_execution_time_value = Mage::helper('M2ePro')->__('unlimited');
            $this->max_execution_time_validation = true;
        } else {
            $this->max_execution_time_value = $max_execution_time_value;
            $this->max_execution_time_validation = $max_execution_time_value >= 360;
        }
        //------------------------------

        //------------------------------
        $this->json_validation = function_exists('json_encode');
        $this->json_value = ($this->json_validation ? Mage::helper('M2ePro')->__('enabled') : Mage::helper('M2ePro')->__('disabled'));
        //------------------------------

        //------------------------------
        $this->simple_xml_validation = class_exists('SimpleXMLElement');
        $this->simple_xml_value = ($this->simple_xml_validation ? Mage::helper('M2ePro')->__('enabled') : Mage::helper('M2ePro')->__('disabled'));
        //------------------------------

        //------------------------------
        $this->curl_validation = function_exists('curl_init');
        $this->curl_value = ($this->curl_validation ? Mage::helper('M2ePro')->__('enabled') : Mage::helper('M2ePro')->__('disabled'));
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Start Configuration'),
                                'onclick' => 'WizardHandlersObj.setStatus('.Ess_M2ePro_Model_Wizard::STATUS_AUTO_SETTINGS.",'setLocation(\'".$this->getUrl('*/*/installation')."\');');",
                                'class' => 'start_installation'
                            ) );
        $this->setChild('start_installation',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }
}