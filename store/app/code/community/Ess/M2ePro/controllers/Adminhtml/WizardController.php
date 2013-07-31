<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_WizardController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/wizard')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Wizard'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/WizardHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay');
    }

    //#############################################

    public function indexAction()
    {
        if (Mage::getModel('M2ePro/Wizard')->isWelcome()) {
            $this->_redirect('*/*/welcome');
            return;
        }

        if (Mage::getModel('M2ePro/Wizard')->isActive()) {
            $this->_redirect('*/*/installation');
            return;
        }

        if (Mage::getModel('M2ePro/Wizard')->isFinished()) {
            $this->_redirect('*/*/congratulation');
            return;
        }

        $this->_redirect('*/adminhtml_about/index');
    }

    //---------------------------

    public function welcomeAction()
    {
        if (!Mage::getModel('M2ePro/Wizard')->isWelcome()) {
            $this->_redirect('*/*/index');
            return;
        }

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_wizard_welcome'))
             ->renderLayout();
    }

    public function installationAction()
    {
        if (!Mage::getModel('M2ePro/Wizard')->isActive()) {
            $this->_redirect('*/*/index');
            return;
        }
        
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_wizard_installation'))
             ->renderLayout();
    }

    public function congratulationAction()
    {
        if (!Mage::getModel('M2ePro/Wizard')->isFinished()) {
            $this->_redirect('*/*/index');
            return;
        }

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_wizard_congratulation'))
             ->renderLayout();
    }

    //#############################################

    public function skipAction()
    {
        Mage::getModel('M2ePro/Wizard')->setStatus(Ess_M2ePro_Model_Wizard::STATUS_SKIP);
        Mage::getModel('M2ePro/Wizard')->clearMenuCache();
        $this->_redirect('*/*/congratulation');
    }

    public function completeAction()
    {
        Mage::getModel('M2ePro/Wizard')->setStatus(Ess_M2ePro_Model_Wizard::STATUS_COMPLETE);
        Mage::getModel('M2ePro/Wizard')->clearMenuCache();
        $this->_redirect('*/*/congratulation');
    }

    //#############################################
    
    public function setStatusAction()
    {
        $status = $this->getRequest()->getParam('status');
        $status && Mage::getModel('M2ePro/Wizard')->setStatus($status);

        if (Mage::getModel('M2ePro/Wizard')->isFinished()) {
            Mage::getModel('M2ePro/Wizard')->clearMenuCache();
        }
    }

    public function startMigrationAction()
    {
        Mage::getModel('M2ePro/Migration_Dispatcher')->process();
    }

    //#############################################
}