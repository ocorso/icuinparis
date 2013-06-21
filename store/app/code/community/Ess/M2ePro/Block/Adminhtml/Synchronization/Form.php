<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Synchronization_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('synchronizationForm');
        //------------------------------

        $this->setTemplate('M2ePro/configuration/synchronization.phtml');
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
        $templates = Mage::helper('M2ePro/Module')->getConfig()->getAllGroupValues('/synchronization/settings/templates/');

        $templates['inspector'] = array();
        $templates['inspector']['mode'] = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/templates/inspector/','mode');
        $templates['inspector']['interval'] = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/templates/inspector/','interval')/60;

        $this->templates = $templates;
        
        $this->orders = Mage::helper('M2ePro/Module')->getConfig()->getAllGroupValues('/synchronization/settings/orders/');
        $this->feedbacks = Mage::helper('M2ePro/Module')->getConfig()->getAllGroupValues('/synchronization/settings/feedbacks/');
        $this->ebay_listings = Mage::helper('M2ePro/Module')->getConfig()->getAllGroupValues('/synchronization/settings/ebay_listings/');
        $this->messages = Mage::helper('M2ePro/Module')->getConfig()->getAllGroupValues('/synchronization/settings/messages/');
        //----------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                                'onclick' => 'SynchronizationHandlersObj.saveSettings(\'runNowTemplates\');',
                                'class' => 'templates_run_now'
                            ) );
        $this->setChild('templates_run_now',$buttonBlock);

        $tempStr = Mage::helper('adminhtml')->__('Are you sure?');

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                                'onclick' => 'deleteConfirm(\''.$tempStr.'\', \'' . $this->getUrl('*/adminhtml_synchronization/clearLog',array('synch_task'=>Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_TEMPLATES)) . '\')',
                                'class' => 'templates_clear_log'
                            ) );
        $this->setChild('templates_clear_log',$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('View Log'),
                                'onclick' => 'setLocation(\''.$this->getUrl('*/adminhtml_logs/synchronizations',array('synch_task'=>Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_TEMPLATES,'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_synchronization/index'))).'\')',
                                'class' => 'button_link'
                            ) );
        $this->setChild('templates_view_log',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                                'onclick' => 'SynchronizationHandlersObj.saveSettings(\'runNowOrders\');',
                                'class' => 'orders_run_now'
                            ) );
        $this->setChild('orders_run_now',$buttonBlock);

        $tempStr = Mage::helper('adminhtml')->__('Are you sure?');

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                                'onclick' => 'deleteConfirm(\''. $tempStr.'\', \'' . $this->getUrl('*/adminhtml_synchronization/clearLog',array('synch_task'=>Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_ORDERS)) . '\')',
                                'class' => 'orders_clear_log'
                            ) );
        $this->setChild('orders_clear_log',$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('View Log'),
                                'onclick' => 'setLocation(\''.$this->getUrl('*/adminhtml_logs/synchronizations',array('synch_task'=>Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_ORDERS,'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_synchronization/index'))).'\')',
                                'class' => 'button_link'
                            ) );
        $this->setChild('orders_view_log',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                                'onclick' => 'SynchronizationHandlersObj.saveSettings(\'runNowFeedbacks\');',
                                'class' => 'feedbacks_run_now'
                            ) );
        $this->setChild('feedbacks_run_now',$buttonBlock);

        $tempStr = Mage::helper('adminhtml')->__('Are you sure?');

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                                'onclick' => 'deleteConfirm(\''. $tempStr.'\', \'' . $this->getUrl('*/adminhtml_synchronization/clearLog',array('synch_task'=>Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_FEEDBACKS)) . '\')',
                                'class' => 'feedbacks_clear_log'
                            ) );
        $this->setChild('feedbacks_clear_log',$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('View Log'),
                                'onclick' => 'setLocation(\''.$this->getUrl('*/adminhtml_logs/synchronizations',array('synch_task'=>Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_FEEDBACKS,'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_synchronization/index'))).'\')',
                                'class' => 'button_link'
                            ) );
        $this->setChild('feedbacks_view_log',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                                'onclick' => 'SynchronizationHandlersObj.saveSettings(\'runNowEbayListings\');',
                                'class' => 'ebay_listings_run_now'
                            ) );
        $this->setChild('ebay_listings_run_now',$buttonBlock);

        $tempStr = Mage::helper('adminhtml')->__('Are you sure?');

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                                'onclick' => 'deleteConfirm(\''. $tempStr.'\', \'' . $this->getUrl('*/adminhtml_synchronization/clearLog',array('synch_task'=>Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_EBAY_LISTINGS)) . '\')',
                                'class' => 'orders_clear_log'
                            ) );
        $this->setChild('ebay_listings_clear_log',$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('View Log'),
                                'onclick' => 'setLocation(\''.$this->getUrl('*/adminhtml_logs/synchronizations',array('synch_task'=>Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_EBAY_LISTINGS,'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_synchronization/index'))).'\')',
                                'class' => 'button_link'
                            ) );
        $this->setChild('ebay_listings_view_log',$buttonBlock);
        //-------------------------------

        //-------------------------------
        // TODO uncomment code
//        $buttonBlock = $this->getLayout()
//                            ->createBlock('adminhtml/widget_button')
//                            ->setData( array(
//                                'label'   => Mage::helper('M2ePro')->__('Run Now'),
//                                'onclick' => 'SynchronizationHandlersObj.saveSettings(\'runNowMessages\');',
//                                'class' => 'messages_run_now'
//                            ) );
//        $this->setChild('messages_run_now',$buttonBlock);
//
//        $tempStr = Mage::helper('adminhtml')->__('Are you sure?');
//
//        $buttonBlock = $this->getLayout()
//                            ->createBlock('adminhtml/widget_button')
//                            ->setData( array(
//                                'label'   => Mage::helper('M2ePro')->__('Clear Log'),
//                                'onclick' => 'deleteConfirm(\''. $tempStr.'\', \'' . $this->getUrl('*/adminhtml_synchronization/clearLog',array('synch_task'=>Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_MESSAGES)) . '\')',
//                                'class' => 'messages_clear_log'
//                            ) );
//        $this->setChild('messages_clear_log',$buttonBlock);
//
//        $buttonBlock = $this->getLayout()
//                            ->createBlock('adminhtml/widget_button')
//                            ->setData( array(
//                                'label'   => Mage::helper('M2ePro')->__('View Log'),
//                                'onclick' => 'setLocation(\''.$this->getUrl('*/adminhtml_logs/synchronizations',array('synch_task'=>Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_MESSAGES,'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_synchronization/index'))).'\')',
//                                'class' => 'button_link'
//                            ) );
//        $this->setChild('messages_view_log',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}