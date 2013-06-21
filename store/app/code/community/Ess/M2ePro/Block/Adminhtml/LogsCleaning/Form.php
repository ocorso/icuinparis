<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_LogsCleaning_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('logsCleaningForm');
        //------------------------------

        $this->setTemplate('M2ePro/configuration/logs_cleaning.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        //----------------------------
        $modes = array();
        $modes[Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS] = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/logs/cleaning/'.Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS.'/','mode');
        $modes[Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS] = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/logs/cleaning/'.Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS.'/','mode');
        $modes[Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS] = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/logs/cleaning/'.Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS.'/','mode');
        $this->modes = $modes;

        $days = array();
        $days[Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS] = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/logs/cleaning/'.Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS.'/','days');
        $days[Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS] = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/logs/cleaning/'.Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS.'/','days');
        $days[Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS] = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/logs/cleaning/'.Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS.'/','days');
        $this->days = $days;
        //----------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                                'onclick' => 'LogsCleaningHandlersObj.runNowLog(\''.Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS.'\')',
                                'class' => 'run_now_'.Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS
                            ) );
        $this->setChild('run_now_'.Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS,$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Clear All'),
                                'onclick' => 'LogsCleaningHandlersObj.clearAllLog(\''.Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS.'\')',
                                'class' => 'clear_all_'.Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS
                            ) );
        $this->setChild('clear_all_'.Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS,$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('View Log'),
                                'onclick' => 'setLocation(\''.$this->getUrl('*/adminhtml_logs/listings',array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_logsCleaning/index'))).'\')',
                                'class' => 'button_link'
                            ) );
        $this->setChild('view_log_'.Ess_M2ePro_Model_LogsCleaning::LOG_LISTINGS,$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                                'onclick' => 'LogsCleaningHandlersObj.runNowLog(\''.Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS.'\')',
                                'class' => 'run_now_'.Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS
                            ) );
        $this->setChild('run_now_'.Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS,$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Clear All'),
                                'onclick' => 'LogsCleaningHandlersObj.clearAllLog(\''.Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS.'\')',
                                'class' => 'clear_all_'.Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS
                            ) );
        $this->setChild('clear_all_'.Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS,$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('View Log'),
                                'onclick' => 'setLocation(\''.$this->getUrl('*/adminhtml_logs/ebayListings',array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_logsCleaning/index'))).'\')',
                                'class' => 'button_link'
                            ) );
        $this->setChild('view_log_'.Ess_M2ePro_Model_LogsCleaning::LOG_EBAY_LISTINGS,$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                                'onclick' => 'LogsCleaningHandlersObj.runNowLog(\''.Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS.'\')',
                                'class' => 'run_now_'.Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS
                            ) );
        $this->setChild('run_now_'.Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS,$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Clear All'),
                                'onclick' => 'LogsCleaningHandlersObj.clearAllLog(\''.Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS.'\')',
                                'class' => 'clear_all_'.Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS
                            ) );
        $this->setChild('clear_all_'.Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS,$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('View Log'),
                                'onclick' => 'setLocation(\''.$this->getUrl('*/adminhtml_logs/synchronizations',array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_logsCleaning/index'))).'\')',
                                'class' => 'button_link'
                            ) );
        $this->setChild('view_log_'.Ess_M2ePro_Model_LogsCleaning::LOG_SYNCHRONIZATIONS,$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}