<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_AccountsController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/configuration')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('eBay Accounts'));

        $this->getLayout()->getBlock('head')
             ->setCanLoadExtJs(true)
             ->addJs('M2ePro/Configuration/AccountsHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/configuration/ebay_accounts');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_accounts'))
             ->renderLayout();
    }

    public function gridAccountsAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_accounts_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function newAction()
    {
        $this->_forward('edit');

        if (Mage::getModel('M2ePro/Migration_Dispatcher')->isUserInterfaceActiveNow()) {
            
            $reset = (bool)$this->getRequest()->getParam('migration_start',false);
            $tempOldAccount = Mage::getModel('M2ePro/Migration_Objects_Accounts')->migrateGetCurrentAccount($reset);

            while ($tempOldAccount !== false && !is_null($tempOldAccount)) {

                $tempCollection = Mage::getModel('M2ePro/Accounts')->getCollection();
                $tempCollection->addFieldToFilter('title', $tempOldAccount['account']);
                $tempCollection->addFieldToFilter('mode', (int)$tempOldAccount['mode']);
                $tempItems = $tempCollection->toArray();

                if ((int)$tempItems['totalRecords'] <= 0) {
                    break;
                }

                Mage::getModel('M2ePro/Migration_Objects_Accounts')->migrateCompleteCurrentAccount();
                Mage::getModel('M2ePro/Migration_TempDbTable')->addValue('accounts.id', $tempOldAccount['id'], $tempItems['items'][0]['id']);
                $tempOldAccount = Mage::getModel('M2ePro/Migration_Objects_Accounts')->migrateGetCurrentAccount(false);
            }

            Mage::register('migration_account',$tempOldAccount);
        }
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Accounts')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist'));
            return $this->_redirect('*/*/index');
        }

        Mage::register('M2ePro_data', $model);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_accounts_edit'))
             ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_accounts_edit_tabs'))
             ->renderLayout();
    }

    //#############################################

    public function beforeGetTokenAction()
    {
        // Get and save form data
        //-------------------------------
        $accountId = $this->getRequest()->getParam('id')?(int)$this->getRequest()->getParam('id'):0;
        $accountTitle = strip_tags($this->getRequest()->getParam('title'));
        $accountMode = $this->getRequest()->getParam('mode')?(int)$this->getRequest()->getParam('mode'):Ess_M2ePro_Model_Accounts::MODE_SANDBOX;
        //-------------------------------

        // Get and save session id
        //-------------------------------
        $mode = $accountMode == Ess_M2ePro_Model_Accounts::MODE_PRODUCTION ?
                                Ess_M2ePro_Model_Connectors_Ebay_Abstract::MODE_PRODUCTION :
                                Ess_M2ePro_Model_Connectors_Ebay_Abstract::MODE_SANDBOX;

        try {
            $response = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                            ->processVirtual('account','get','authUrl',
                                              array('back_url'=>$this->getUrl('*/*/afterGetToken')),
                                              NULL,NULL,NULL,$mode);
        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}
            
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('The eBay token obtaining is currently unavailable. Please try again later.'));
            $this->_redirect('*/*/index');
            return;
        }

        $_SESSION['get_token_account_id'] = $accountId;
        $_SESSION['get_token_account_title'] = $accountTitle;
        $_SESSION['get_token_account_mode'] = $accountMode;

        $_SESSION['get_token_session_id'] = $response['session_id'];

        $this->_redirectUrl($response['url']);
        //-------------------------------
    }

    public function afterGetTokenAction()
    {
        // Get ebay session id
        //-------------------------------
        if (!isset($_SESSION['get_token_session_id'])) {
            $this->_redirect('*/*/index');
        }
        $sessionId = $_SESSION['get_token_session_id'];
        unset($_SESSION['get_token_session_id']);
        //-------------------------------

        // Get account form data
        //-------------------------------
        $_SESSION['get_token_account_token_session'] = $sessionId;
        //-------------------------------

        // Goto account add or edit page
        //-------------------------------
        $accountId = (int)$_SESSION['get_token_account_id'];
        unset($_SESSION['get_token_account_id']);

        if ($accountId == 0) {
            $this->_redirect('*/*/new');
        } else {
            $this->_redirect('*/*/edit/id/'.$accountId);
        }
        //-------------------------------
    }

    //#############################################

    public function checkCustomerIdAction()
    {
        exit(json_encode(array(
            'ok' => (bool)Mage::getModel('customer/customer')->load($this->getRequest()->getParam('customer_id'))->getId()
        )));
    }

    //#############################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/*/index');
        }

        $id = $this->getRequest()->getParam('id');

        // Base prepare
        //--------------------
        $data = array();
        //--------------------

        // tab: general
        //--------------------
        $keys = array(
            'title',
            'mode',
            'token_session',
            'ebay_listings_synchronization',
            'messages_receive'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        //--------------------

        // tab: orders
        //--------------------
        $keys = array(
            'orders_mode',
            'orders_listings_mode',
            'orders_listings_store_mode',
            'orders_listings_store_id',
            'orders_ebay_mode',
            'orders_ebay_create_product',
            'orders_ebay_store_id',
            'orders_customer_mode',
            'orders_customer_exist_id',
            'orders_customer_new_website',
            'orders_customer_new_group',
            'orders_customer_new_subscribe_news',

            'orders_status_checkout_incomplete',
            'orders_status_payment_complete_mode',
            'orders_combined_mode',
            'orders_status_mode',
            'orders_status_checkout_completed',
            'orders_status_payment_completed',
            'orders_status_shipping_completed',

            'orders_status_invoice',
            'orders_status_shipping'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        if (isset($data['orders_listings_store_id']) && $data['orders_listings_store_id'] == '') {
            $data['orders_listings_store_id'] = 0;
        }

        if (isset($data['orders_ebay_store_id']) && $data['orders_ebay_store_id'] == '') {
            $data['orders_ebay_store_id'] = 0;
        }

        if (isset($data['orders_customer_exist_id']) && $data['orders_customer_exist_id'] == '') {
            $data['orders_customer_exist_id'] = NULL;
        }

        if (isset($data['orders_customer_new_website']) && $data['orders_customer_new_website'] == '') {
            $data['orders_customer_new_website'] = NULL;
        }

        if (isset($data['orders_customer_new_group']) && $data['orders_customer_new_group'] == '') {
            $data['orders_customer_new_group'] = NULL;
        }

        if (isset($post['orders_customer_new_send_notifications'])) {

            $keys = array('a','o','i');

            $data['orders_customer_new_send_notifications'] = '';
            foreach ($keys as $key=>$value) {
                $data['orders_customer_new_send_notifications'] .= ($data['orders_customer_new_send_notifications'] == '' ? '' : '_');
                if (array_search($value,$post['orders_customer_new_send_notifications']) !== false) {
                    $data['orders_customer_new_send_notifications'] .= $value.'1';
                } else {
                    $data['orders_customer_new_send_notifications'] .= $value.'0';
                }
            }

        } else {
            $data['orders_customer_new_send_notifications'] = 'a0_o0_i0';
        }

        if (!isset($data['orders_combined_mode']) ||
            $data['orders_status_checkout_incomplete'] != Ess_M2ePro_Model_Accounts::ORDERS_CHECKOUT_MODE_IGNORE ||
            $data['orders_status_payment_complete_mode'] != Ess_M2ePro_Model_Accounts::ORDERS_PAYMENT_MODE_IGNORE) {
            $data['orders_combined_mode'] = Ess_M2ePro_Model_Accounts::ORDERS_COMBINED_MODE_YES;
        }

        if (isset($data['orders_status_mode']) && $data['orders_status_mode'] == Ess_M2ePro_Model_Accounts::ORDERS_STATUS_MAPPING_CUSTOM) {
            if (!isset($data['orders_status_invoice']) || $data['orders_status_invoice'] != 'on') {
                $data['orders_status_invoice'] = Ess_M2ePro_Model_Accounts::ORDERS_INVOICE_MODE_NO;
            } else {
                $data['orders_status_invoice'] = Ess_M2ePro_Model_Accounts::ORDERS_INVOICE_MODE_YES;
            }
            if (!isset($data['orders_status_shipping']) || $data['orders_status_shipping'] != 'on') {
                $data['orders_status_shipping'] = Ess_M2ePro_Model_Accounts::ORDERS_SHIPMENT_MODE_NO;
            } else {
                $data['orders_status_shipping'] = Ess_M2ePro_Model_Accounts::ORDERS_SHIPMENT_MODE_YES;
            }
        } else {
            $data['orders_status_invoice'] = Ess_M2ePro_Model_Accounts::ORDERS_INVOICE_MODE_YES;
            $data['orders_status_shipping'] = Ess_M2ePro_Model_Accounts::ORDERS_SHIPMENT_MODE_YES;
        }
        //--------------------

        // tab: feedbacks
        //--------------------
        $keys = array(
            'feedbacks_receive',
            'feedbacks_auto_response',
            'feedbacks_auto_response_only_positive'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        //--------------------

        // Add or update server
        //--------------------
        $requestMode = $data['mode'] == Ess_M2ePro_Model_Accounts::MODE_PRODUCTION ?
                       Ess_M2ePro_Model_Connectors_Ebay_Abstract::MODE_PRODUCTION :
                       Ess_M2ePro_Model_Connectors_Ebay_Abstract::MODE_SANDBOX;
        
        if ((bool)$id) {

            $requestTempParams = array(
                'title' => $data['title'],
                'mode' => $requestMode,
                'token_session' => $data['token_session']
            );
            $response = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                        ->processVirtual('account','update','entity',
                                          $requestTempParams,
                                          NULL,NULL,$id);
        } else {

            $requestTempParams = array(
                'title' => $data['title'],
                'mode' => $requestMode,
                'token_session' => $data['token_session']
            );
            $response = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                        ->processVirtual('account','add','entity',
                                          $requestTempParams,
                                          NULL,NULL,NULL,$requestMode);
        }

        if (!isset($response['token_expired_date'])) {
            throw new Exception('Account is not added or updated. Try again later.');
        }
        
        isset($response['hash']) && $data['server_hash'] = $response['hash'];

        $data['ebay_info'] = json_encode($response['info']);
        $data['token_expired_date'] = $response['token_expired_date'];
        //--------------------

        // Change token
        //--------------------
        $isChangeTokenSession = false;
        if ((bool)$id) {
            $oldTokenSession = Mage::getModel('M2ePro/Accounts')->loadInstance($id)->getTokenSession();
            $newTokenSession = $data['token_session'];
            if ($newTokenSession != $oldTokenSession) {
                $isChangeTokenSession = true;
            }
        } else {
            $isChangeTokenSession = true;
        }
        //--------------------

        // Add or update model
        //--------------------
        $model = Mage::getModel('M2ePro/Accounts');
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->load($id)->addData($data);
        $id = $model->save()->getId();
        //--------------------

        // Update ebay store
        //--------------------
        if ($isChangeTokenSession) {
            $this->ebayStoreUpdateAction(false,$id);
        }
        //--------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Account was successfully saved'));

        if (Mage::getModel('M2ePro/Migration_Dispatcher')->isUserInterfaceActiveNow()) {
            
            $temp = Mage::getModel('M2ePro/Migration_Objects_Accounts')->migrateGetCurrentAccount(false);
            Mage::getModel('M2ePro/Migration_Objects_Accounts')->migrateCompleteCurrentAccount();
            Mage::getModel('M2ePro/Migration_TempDbTable')->addValue('accounts.id', $temp['id'], $id);

            $this->_redirect('*/*/new');
            return;
        }

        //if (Mage::getModel('M2ePro/Wizard')->isActive() &&
        //    Mage::getModel('M2ePro/Wizard')->getStatus() == Ess_M2ePro_Model_Wizard::STATUS_ACCOUNTS) {
        //    $this->_redirect('*/*/new');
        //    return;
        //}

        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to remove'));
            return $this->_redirect('*/*/index');
        }

        $idsForDelete = array();
        !is_null($id) && $idsForDelete[] = (int)$id;
        !is_null($ids) && $idsForDelete = array_merge($idsForDelete,(array)$ids);

        $deleted = $locked = 0;
        foreach ($idsForDelete as $id) {
            $template = Mage::getModel('M2ePro/Accounts')->loadInstance($id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                try {
                    Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                        ->processVirtual('account','delete','entity',
                                          array(),
                                          NULL,NULL,$template->getId());
                } catch (Exception $e) {
                    $template->deleteInstance();
                    $deleted++;
                    throw $e;
                }
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%count% record(s) were successfully deleted.');
        $deleted && $this->_getSession()->addSuccess(str_replace('%count%',$deleted,$tempString));

        $tempString = Mage::helper('M2ePro')->__('%count% record(s) are in use in General Template(s). Account must not be in use.');
        $locked && $this->_getSession()->addError(str_replace('%count%',$locked,$tempString));

        $this->_redirect('*/*/index');
    }
    
    //#############################################

    public function gridFeedbacksTemplatesAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Accounts')->load($id);

        if (!$model->getId() && $id) {
            echo Mage::helper('M2ePro')->__('Account does not exist');
            return;
        }

        Mage::register('M2ePro_data', $model);

        // Response for grid
        //----------------------------
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_accounts_edit_tabs_feedbacks_grid')->toHtml();
        $this->getResponse()->setBody($response);
        //----------------------------
    }

    public function feedbacksTemplatesCheckAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Accounts')->load($id);

        exit(json_encode(array(
            'ok' => (bool)$model->hasFeedbacksTemplates()
        )));
    }

    public function feedbacksTemplatesEditAction()
    {
        $id = $this->getRequest()->getParam('id');
        $accountId = $this->getRequest()->getParam('account_id');
        $body = $this->getRequest()->getParam('body');

        $data = array('account_id'=>$accountId,'body'=>$body);

        $model = Mage::getModel('M2ePro/FeedbacksTemplates');
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->load($id)->addData($data);
        $id = $model->save()->getId();

        exit('ok');
    }

    public function feedbacksTemplatesDeleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        Mage::getModel('M2ePro/FeedbacksTemplates')->loadInstance($id)->deleteInstance();
        exit('ok');
    }

    //#############################################

    public function ebayStoreUpdateAction($save = true, $id = false)
    {
        $save && $this->saveAction();
        $id || $id = $this->getRequest()->getParam('id');

        $accountModel = Mage::getModel('M2ePro/Accounts')->loadInstance($id);
        $accountModel->updateEbayStoreInfo();
    }

    //#############################################
}