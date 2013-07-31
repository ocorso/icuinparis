<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_LicenseController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/configuration')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('License'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Configuration/LicenseHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/configuration/license');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_license'))
             ->renderLayout();
    }

    //#############################################

    public function refreshStatusAction()
    {
        Mage::getModel('M2ePro/License_Server')->updateStatus(true);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The license status has been successfully refreshed.'));
        $this->_redirect('*/*/index');
    }

    public function confirmKeyAction()
    {
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            // Save settings
            //--------------------
            Mage::getModel('M2ePro/License_Model')->setKey($post['key']);
            //--------------------

            Mage::getModel('M2ePro/License_Server')->updateStatus(true);

            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The license key has been successfully updated.'));
        }
        $this->_redirect('*/*/index');
    }

    public function checkLicenseAction()
    {
        exit(json_encode(array(
            'ok' => !(bool)Mage::getModel('M2ePro/License_Model')->isNoneMode()
        )));
    }

    //#############################################
}