<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_SynchronizationTemplates_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('synchronizationTemplatesTabs');
        //------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('tab_general', array(
                'label'   => Mage::helper('M2ePro')->__('General'),
                'title'   => Mage::helper('M2ePro')->__('General'),
                'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_synchronizationTemplates_edit_tabs_general')->toHtml(),
            ))
            ->addTab('tab_revise', array(
                'label'   => Mage::helper('M2ePro')->__('Revise Rules'),
                'title'   => Mage::helper('M2ePro')->__('Revise Rules'),
                'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_synchronizationTemplates_edit_tabs_revise')->toHtml(),
            ))
            ->addTab('tab_relist', array(
                'label'   => Mage::helper('M2ePro')->__('Relist Rules'),
                'title'   => Mage::helper('M2ePro')->__('Relist Rules'),
                'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_synchronizationTemplates_edit_tabs_relist')->toHtml(),
            ))
            ->addTab('tab_stop', array(
                'label'   => Mage::helper('M2ePro')->__('Stop Rules'),
                'title'   => Mage::helper('M2ePro')->__('Stop Rules'),
                'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_synchronizationTemplates_edit_tabs_stop')->toHtml(),
            ))
            ->setActiveTab($this->getRequest()->getParam('tab', 'tab_general'));

        return parent::_beforeToHtml();
    }
}