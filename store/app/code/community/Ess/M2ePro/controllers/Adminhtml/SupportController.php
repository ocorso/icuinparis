<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_SupportController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/help')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Help'))
             ->_title(Mage::helper('M2ePro')->__('Support'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/SupportHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/help/support');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_support'))
             ->renderLayout();
    }

    //#############################################

    public function getUserVoiceDataAction()
    {
        $userVoiceEnabled = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/uservoice/', 'mode');

        if (!$userVoiceEnabled) {
            exit(json_encode(array()));
        }

        $query = $this->getRequest()->getParam('search', null);

        if (!is_null($query)) {

            $query = strip_tags($query);

            $userVoiceApiUrl = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/uservoice/', 'baseurl');
            $action = 'articles/search.json';
            $params = array(
                'client' => Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/uservoice/', 'client_key'),
                'query' => $query,
                'page' => 1,
                'per_page' => 10
            );

            $response = $this->sendRequestAsGet($userVoiceApiUrl, $action, $params);

            if ($response === false) {
                exit(json_encode(array()));
            }

            exit($response);
        }

        $articlesBackupKey = Mage::helper('M2ePro/Module')->getName().'_BACKUP_USERVOICE_ARTICLES';
        $articlesBackup = Mage::app()->getCache()->load($articlesBackupKey);

        if ($articlesBackup === false) {
            
            $userVoiceApiUrl = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/uservoice/', 'baseurl');
            $action = 'articles.json';
            $params = array(
                'client' => Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/uservoice/', 'client_key'),
                'page' => 1,
                'per_page' => 10
            );

            $response = $this->sendRequestAsGet($userVoiceApiUrl, $action, $params);

            if ($response === false) {
                exit(json_encode(array()));
            }

            Mage::app()->getCache()->save(serialize($response), $articlesBackupKey, array(), 60*60*24);
            exit($response);
        }

        exit(unserialize($articlesBackup));
    }

    //---------------------------------------------

    private function sendRequestAsGet($baseurl, $action, $params)
    {
        $curlObject = curl_init();

        //set the server we are using
        curl_setopt($curlObject, CURLOPT_URL, $baseurl . $action . '?'.http_build_query($params,'','&'));

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);
        curl_setopt($curlObject, CURLOPT_POST, false);

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 300);

        $response = curl_exec($curlObject);
        curl_close($curlObject);

        return $response;
    }

    //#############################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/*/index');
        }

        $keys = array(
            'subject',
            'contact_mail',
            'contact_name',
            'type',
            'description'
        );

        $data = array();
        foreach ($keys as $key) {
            if (!isset($post[$key])) {
                $this->_getSession()->addError(Mage::helper('M2ePro')->__('You should fill in all required fields.'));
                return $this->_redirect('*/*/index');
            }
            $data[$key] = $post[$key];
        }

        $toEmail   = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', $data['type'].'_mail');
        $fromEmail = $data['contact_mail'];
        $fromName  = $data['contact_name'];
        $subject   = $data['subject'];
        $body      = $this->createBody($data['type'],$data['subject'],$data['description']);

        $attachments = array();

        if (isset($_FILES['files'])) {
            foreach ($_FILES['files']['name'] as $key => $uploadFileName) {
                if ('' == $uploadFileName) {
                    continue;
                }

                $realName = $uploadFileName;
                $tempPath = $_FILES['files']['tmp_name'][$key];
                $mimeType = $_FILES['files']['type'][$key];

                $attachment = new Zend_Mime_Part(file_get_contents($tempPath));
                $attachment->type        = $mimeType;
                $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding    = Zend_Mime::ENCODING_BASE64;
                $attachment->filename    = $realName;

                $attachments[] = $attachment;
            }
        }

        $this->sendMail($toEmail, $fromEmail, $fromName, $subject, $body, $attachments);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Your message has been successfully sent.'));
        $this->_redirect('*/*/index');
    }

    private function createBody($type, $subject, $description)
    {
        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        $body = <<<DATA

{$description}

-------------------------------- GENERAL -----------------------------------------
Date: {$currentDate}
Type: {$type}
Subject: {$subject}


DATA;

        $body .= Mage::helper('M2ePro/Module')->getSummaryTextInfo();

        return $body;
    }

    private function sendMail($toEmail, $fromEmail, $fromName, $subject, $body, array $attachments = array())
    {
        $mail = new Zend_Mail('UTF-8');

        $mail->addTo($toEmail)
             ->setFrom($fromEmail, $fromName)
             ->setSubject($subject)
             ->setBodyText($body, null, Zend_Mime::ENCODING_8BIT);

        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        $mail->send();
    }

    //#############################################
}