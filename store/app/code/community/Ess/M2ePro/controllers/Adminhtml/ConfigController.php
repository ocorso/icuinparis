<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_ConfigController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/development')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Development'))
             ->_title(Mage::helper('M2ePro')->__('Config'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/ConfigHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay');
    }

    //#############################################

    public function indexAction()
    {
        $this->_redirect('*/*/m2epro');
    }

    public function essAction()
    {
        Mage::register('m2epro_config_mode','ess');
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_config','',array('mode'=>'ess')))
             ->renderLayout();
    }

    public function m2eproAction()
    {
        Mage::register('m2epro_config_mode','m2epro');
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_config','',array('mode'=>'m2epro')))
             ->renderLayout();
    }

    //#############################################

    public function gridViewAction()
    {
        Mage::register('m2epro_config_mode',$this->getRequest()->getParam('mode'));
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_config_view_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function saveAction()
    {
        Mage::register('m2epro_config_mode',$this->getRequest()->getParam('mode'));
        Mage::registry('m2epro_config_mode') == 'ess' && $ormConfig = 'M2ePro/Ess';
        Mage::registry('m2epro_config_mode') == 'm2epro' && $ormConfig = 'M2ePro/Module';

        $mode = $this->getRequest()->getParam('config_mode');
        $id = $this->getRequest()->getParam('config_id');

        $group = $this->getRequest()->getParam('config_group');

        if ($group == '') {
            $group = NULL;
        }

        $key = $this->getRequest()->getParam('config_key');
        $value = $this->getRequest()->getParam('config_value');
        $notice = $this->getRequest()->getParam('config_notice');

        if ($notice == '') {
            $notice = NULL;
        }

        if ($mode == 'edit') {

            Mage::registry('m2epro_config_mode') == 'ess' && $ormModelConfig = 'M2ePro/EssConfig';
            Mage::registry('m2epro_config_mode') == 'm2epro' && $ormModelConfig = 'M2ePro/Config';

            $tempCollection = Mage::getModel($ormModelConfig)->getCollection()
                                                             ->addFieldToFilter('`id`', $id)
                                                             ->toArray();
            $data = $tempCollection['items'][0];

            if ($data['group'] != $group || $data['key'] != $key) {
                if (is_null($data['group'])) {
                    Mage::helper($ormConfig)->getConfig()->deleteGlobalValue($data['key']);
                } else {
                    Mage::helper($ormConfig)->getConfig()->deleteGroupValue($data['group'], $data['key']);
                }
            }
        }

        if (is_null($group)) {
            Mage::helper($ormConfig)->getConfig()->setGlobalValue($key, $value , $notice);
        } else {
            Mage::helper($ormConfig)->getConfig()->setGroupValue($group, $key, $value , $notice);
        }

        exit();
    }

    public function deleteAction()
    {
        Mage::register('m2epro_config_mode',$this->getRequest()->getParam('mode'));
        Mage::registry('m2epro_config_mode') == 'ess' && $ormConfig = 'M2ePro/Ess';
        Mage::registry('m2epro_config_mode') == 'm2epro' && $ormConfig = 'M2ePro/Module';

        $group = $this->getRequest()->getParam('config_group');

        if ($group == '') {
            $group = NULL;
        }

        $key = $this->getRequest()->getParam('config_key');

        if (is_null($group)) {
            Mage::helper($ormConfig)->getConfig()->deleteGlobalValue($key);
        } else {
            Mage::helper($ormConfig)->getConfig()->deleteGroupValue($group, $key);
        }
        
        exit();
    }

    //############################################# 
}