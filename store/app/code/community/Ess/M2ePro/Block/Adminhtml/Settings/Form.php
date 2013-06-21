<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Settings_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('settingsForm');
        //------------------------------

        $this->setTemplate('M2ePro/configuration/settings.phtml');
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
        // Set data for form
        //----------------------------
        $this->products_show_thumbnails = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/products/settings/','show_thumbnails');
        $this->block_notices_show = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/block_notices/settings/','show');
        $this->feedbacks_notification_mode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/feedbacks/notification/','mode');
        $this->messages_notification_mode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/messages/notification/','mode');
        $this->cron_notification_mode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/notification/','mode');
        //----------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Restore All Helps'),
                                'onclick' => 'confirmSetLocation(\''.Mage::helper('M2ePro')->__('Are you sure?').'\', \''.$this->getUrl('*/*/restoreBlockNotices').'\')',
                                'class' => 'restore_block_notices'
                            ) );
        $this->setChild('restore_block_notices',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                                'onclick' => 'SettingsHandlersObj.process(\''.$this->getUrl('*/adminhtml_accounts/new',array('migration_start'=>'yes')).'\');',
                                'class' => 'start_migration'
                            ) );
        $this->setChild('start_migration',$buttonBlock);
        //-------------------------------
        
        return parent::_beforeToHtml();
    }
}