<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_MessagesController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/communication')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Communication'))
             ->_title(Mage::helper('M2ePro')->__('My Messages'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Communication/MessagesHandlers.js')
             ->addItem('js_css', 'prototype/windows/themes/default.css')
             ->addItem('js_css', 'prototype/windows/themes/magento.css')
             ->addJs('prototype/window.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/communication/my_messages');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction();

        if ((bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/messages/', 'mode')) {
            $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_messages'));
        } else {
            $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_messages_comingSoon'));
        }
        
        $this->renderLayout();
    }
    
    public function gridMessagesAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_messages_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function saveAction()
    {
        $messageId = $this->getRequest()->getParam('message_id');
        $messageText = $this->getRequest()->getParam('message_text');

        $messageModel = Mage::getModel('M2ePro/Messages')->loadInstance($messageId);
        $messageModel->sendResponse($messageText);

        $paramsConnector = array(
            'since_time' => $messageModel->getData('message_date')
        );

        Mage::getModel('M2ePro/Messages')->receiveMessages($messageModel->getAccount(), $paramsConnector);
    }

    //#############################################

    public function getMessageInfoAction()
    {
        $messageId = $this->getRequest()->getParam('message_id');
        $messageModel = Mage::getModel('M2ePro/Messages')->loadInstance($messageId);

        $messageInfo = array();
        $messageInfo['text'] = $messageModel->getData('message_text');
        $messageInfo['responses'] = json_decode($messageModel->getData('message_responses'));

        exit(json_encode(array(
            'message_info' => $messageInfo
        )));
    }

    //#############################################
}