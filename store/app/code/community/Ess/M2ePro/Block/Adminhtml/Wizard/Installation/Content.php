<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Installation_Content extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationContent');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/installation.phtml');
    }

    protected function _beforeToHtml()
    {
        // Set data for form
        //----------------------------
        $this->status = Mage::getModel('M2ePro/Wizard')->getStatus();
        //----------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'WizardHandlersObj.skipStep(\'block_notice_wizard_installation_step_auto_settings\','.Ess_M2ePro_Model_Wizard::STATUS_AUTO_SETTINGS.',\'block_notice_wizard_installation_step_license\','.Ess_M2ePro_Model_Wizard::STATUS_LICENSE.');',
                                'class' => 'skip_auto_settings'
                            ) );
        $this->setChild('skip_auto_settings',$buttonBlock);

        $this->basePath = Mage::helper('M2ePro/Server')->getBaseDirectory();
        $this->baseUrl = Mage::helper('M2ePro/Server')->getBaseUrl();
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                                'onclick' => 'WizardHandlersObj.processStep(\''.$this->getUrl('*/adminhtml_license/index').'\',\'block_notice_wizard_installation_step_license\','.Ess_M2ePro_Model_Wizard::STATUS_LICENSE.',\'block_notice_wizard_installation_step_marketplaces\','.Ess_M2ePro_Model_Wizard::STATUS_MARKETPLACES.');',
                                'class' => 'process_license'
                            ) );
        $this->setChild('process_license',$buttonBlock);
        //-------------------------------

        //-------------------------------
        if (!Mage::helper('M2ePro/Magento')->isMagentoGoMode()) {
            $nextStepId = 'block_notice_wizard_installation_step_migration';
            $nextStepStatus = Ess_M2ePro_Model_Wizard::STATUS_MIGRATION;
        } else {
            $nextStepId = 'block_notice_wizard_installation_step_accounts';
            $nextStepStatus = Ess_M2ePro_Model_Wizard::STATUS_ACCOUNTS;
        }

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                                'onclick' => 'WizardHandlersObj.processStep(\''.$this->getUrl('*/adminhtml_marketplaces/index').'\',\'block_notice_wizard_installation_step_marketplaces\','.Ess_M2ePro_Model_Wizard::STATUS_MARKETPLACES.',\''.$nextStepId.'\','.$nextStepStatus.',\'WizardHandlersObj.callBackAfterMarketplaces();\');',
                                'class' => 'process_marketplaces'
                            ) );
        $this->setChild('process_marketplaces',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                                'onclick' => 'WizardHandlersObj.processStep(\''.$this->getUrl('*/adminhtml_accounts/new',array('migration_start'=>'yes')).'\',\'block_notice_wizard_installation_step_migration\','.Ess_M2ePro_Model_Wizard::STATUS_MIGRATION.',\'block_notice_wizard_installation_step_accounts\','.Ess_M2ePro_Model_Wizard::STATUS_ACCOUNTS.',\'WizardHandlersObj.callBackAfterMigration();\');',
                                'class' => 'process_migration'
                            ) );
        $this->setChild('process_migration',$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Skip'),
                                'onclick' => 'WizardHandlersObj.skipStep(\'block_notice_wizard_installation_step_migration\','.Ess_M2ePro_Model_Wizard::STATUS_MIGRATION.',\'block_notice_wizard_installation_step_accounts\','.Ess_M2ePro_Model_Wizard::STATUS_ACCOUNTS.');',
                                'class' => 'skip_migration'
                            ) );
        $this->setChild('skip_migration',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                                'onclick' => 'WizardHandlersObj.processStep(\''.$this->getUrl('*/adminhtml_accounts/new').'\',\'block_notice_wizard_installation_step_accounts\','.Ess_M2ePro_Model_Wizard::STATUS_ACCOUNTS.',\'block_notice_wizard_installation_step_synchronization\','.Ess_M2ePro_Model_Wizard::STATUS_SYNCHRONIZATION.');',
                                'class' => 'process_accounts'
                            ) );
        $this->setChild('process_accounts',$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Skip'),
                                'onclick' => 'WizardHandlersObj.skipStep(\'block_notice_wizard_installation_step_accounts\','.Ess_M2ePro_Model_Wizard::STATUS_ACCOUNTS.',\'block_notice_wizard_installation_step_synchronization\','.Ess_M2ePro_Model_Wizard::STATUS_SYNCHRONIZATION.');',
                                'class' => 'skip_accounts'
                            ) );
        $this->setChild('skip_accounts',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                                'onclick' => 'WizardHandlersObj.processStep(\''.$this->getUrl('*/adminhtml_synchronization/index').'\',\'block_notice_wizard_installation_step_synchronization\','.Ess_M2ePro_Model_Wizard::STATUS_SYNCHRONIZATION.',undefined,'.Ess_M2ePro_Model_Wizard::STATUS_COMPLETE.',\'WizardHandlersObj.callBackAfterEndConfiguration();\');',
                                'class' => 'process_synchronization'
                            ) );
        $this->setChild('process_synchronization',$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Skip'),
                                'onclick' => 'WizardHandlersObj.skipStep(\'block_notice_wizard_installation_step_synchronization\','.Ess_M2ePro_Model_Wizard::STATUS_SYNCHRONIZATION.',undefined,'.Ess_M2ePro_Model_Wizard::STATUS_COMPLETE.',\'WizardHandlersObj.callBackAfterEndConfiguration();\');',
                                'class' => 'skip_synchronization'
                            ) );
        $this->setChild('skip_synchronization',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Complete Configuration'),
                                'onclick' => 'setLocation(\''.$this->getUrl('*/*/complete').'\');',
                                'class' => 'end_installation'
                            ) );
        $this->setChild('end_installation',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}