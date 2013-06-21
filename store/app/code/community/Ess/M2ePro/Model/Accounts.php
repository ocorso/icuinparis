<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Accounts extends Mage_Core_Model_Abstract
{
    const MODE_SANDBOX    = 0;
    const MODE_PRODUCTION = 1;

    const FEEDBACKS_RECEIVE_NO  = 0;
    const FEEDBACKS_RECEIVE_YES = 1;

    const FEEDBACKS_AUTO_RESPONSE_NONE   = 0;
    const FEEDBACKS_AUTO_RESPONSE_CYCLED = 1;
    const FEEDBACKS_AUTO_RESPONSE_RANDOM = 2;

    const FEEDBACKS_AUTO_RESPONSE_ONLY_POSITIVE_NO  = 0;
    const FEEDBACKS_AUTO_RESPONSE_ONLY_POSITIVE_YES = 1;

    const MESSAGES_RECEIVE_NO  = 0;
    const MESSAGES_RECEIVE_YES = 1;

    const ORDERS_MODE_NO  = 0;
    const ORDERS_MODE_YES = 1;

    const ORDERS_LISTINGS_MODE_NO  = 0;
    const ORDERS_LISTINGS_MODE_YES = 1;

    const ORDERS_LISTINGS_STORE_MODE_NO  = 0;
    const ORDERS_LISTINGS_STORE_MODE_YES = 1;

    const ORDERS_EBAY_MODE_NO  = 0;
    const ORDERS_EBAY_MODE_YES = 1;

    const ORDERS_EBAY_CREATE_PRODUCT_NO  = 0;
    const ORDERS_EBAY_CREATE_PRODUCT_YES = 1;

    const ORDERS_CUSTOMER_MODE_GUEST = 0;
    const ORDERS_CUSTOMER_MODE_EXIST = 1;
    const ORDERS_CUSTOMER_MODE_NEW   = 2;

    const ORDERS_CUSTOMER_NEW_SUBSCRIBE_NEWS_NO  = 0;
    const ORDERS_CUSTOMER_NEW_SUBSCRIBE_NEWS_YES = 1;

    const ORDERS_STATUS_MAPPING_DEFAULT = 0;
    const ORDERS_STATUS_MAPPING_CUSTOM  = 1;

    const ORDERS_DEFAULT_STATUS_ON_CHECKOUT_COMPLETE = 'pending';
    const ORDERS_DEFAULT_STATUS_ON_PAYMENT_COMPLETE  = 'processing';
    const ORDERS_DEFAULT_STATUS_ON_SHIPPING_COMPLETE = 'complete';

    const ORDERS_CHECKOUT_MODE_COMPLETED = 0;
    const ORDERS_CHECKOUT_MODE_IGNORE    = 1;

    const ORDERS_PAYMENT_MODE_IGNORE    = 0;
    const ORDERS_PAYMENT_MODE_COMPLETED = 1;

    const ORDERS_COMBINED_MODE_NO  = 0;
    const ORDERS_COMBINED_MODE_YES = 1;

    const ORDERS_INVOICE_MODE_NO  = 0;
    const ORDERS_INVOICE_MODE_YES = 1;

    const ORDERS_SHIPMENT_MODE_NO  = 0;
    const ORDERS_SHIPMENT_MODE_YES = 1;

    const EBAY_LISTINGS_SYNCHRONIZATION_NO  = 0;
    const EBAY_LISTINGS_SYNCHRONIZATION_YES = 1;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Accounts');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_Accounts
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Account does not exist.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingTemplateId
     * @return Ess_M2ePro_Model_Accounts
     */
    public function loadByListingTemplate($listingTemplateId)
    {
         $tempModel = Mage::getModel('M2ePro/ListingsTemplates')->load($listingTemplateId);

         if (is_null($tempModel->getId())) {
             throw new Exception('General template does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('account_id'));
    }

    /**
     * @throws LogicException
     * @param  int $feedbackId
     * @return Ess_M2ePro_Model_Accounts
     */
    public function loadByFeedback($feedbackId)
    {
         $tempModel = Mage::getModel('M2ePro/Feedbacks')->load($feedbackId);

         if (is_null($tempModel->getId())) {
             throw new Exception('Feedback does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('account_id'));
    }

    /**
     * @throws LogicException
     * @param  int $feedbackTemplateId
     * @return Ess_M2ePro_Model_Accounts
     */
    public function loadByFeedbackTemplate($feedbackTemplateId)
    {
         $tempModel = Mage::getModel('M2ePro/FeedbacksTemplates')->load($feedbackTemplateId);

         if (is_null($tempModel->getId())) {
             throw new Exception('Feedback template does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('account_id'));
    }

    /**
     * @throws LogicException
     * @param  int $messageId
     * @return Ess_M2ePro_Model_Accounts
     */
    public function loadByMessage($messageId)
    {
         $tempModel = Mage::getModel('M2ePro/Messages')->load($messageId);

         if (is_null($tempModel->getId())) {
             throw new Exception('Message does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('account_id'));
    }

    /**
     * @throws LogicException
     * @param  int $ebayListingId
     * @return Ess_M2ePro_Model_Accounts
     */
    public function loadByEbayListing($ebayListingId)
    {
         $tempModel = Mage::getModel('M2ePro/EbayListings')->load($ebayListingId);

         if (is_null($tempModel->getId())) {
             throw new Exception('3rd Party Listing does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('account_id'));
    }

    // ########################################

    /**
     * @return bool
     */
    public function isLocked()
    {
        if (!$this->getId()) {
            return false;
        }

        return (bool)Mage::getModel('M2ePro/ListingsTemplates')
                            ->getCollection()
                            ->addFieldToFilter('account_id', $this->getId())
                            ->getSize();
    }

    /**
     * @return bool
     */
    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $storeCategoriesTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_accounts_store_categories');
        Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                ->delete($storeCategoriesTable,array('account_id = ?'=>$this->getId()));

        $ebayOrdersTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_orders');
        Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                ->delete($ebayOrdersTable,array('account_id = ?'=>$this->getId()));

        $feedbacksTemplates = $this->getFeedbacksTemplates(true);
        foreach ($feedbacksTemplates as $feedbackTemplate) {
            $feedbackTemplate->deleteInstance();
        }

        $feedbacks = $this->getFeedbacks(true);
        foreach ($feedbacks as $feedback) {
            $feedback->deleteInstance();
        }

        $messages = $this->getMessages(true);
        foreach ($messages as $message) {
            $message->deleteInstance();
        }

        $ebayListings = $this->getEbayListings(true);
        foreach ($ebayListings as $ebayListing) {
            $ebayListing->deleteInstance();
        }

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @return array
     */
    public function getListingsTemplates($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/ListingsTemplates')->getCollection();
        $tempCollection->addFieldToFilter('account_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $resultArray[] = Mage::getModel('M2ePro/ListingsTemplates')
                                        ->loadInstance($item['id']);
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @return array
     */
    public function getFeedbacksTemplates($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/FeedbacksTemplates')->getCollection()
                                                                     ->addFieldToFilter('account_id', $this->getId());
        foreach ($filters as $field => $filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $resultArray[] = Mage::getModel('M2ePro/FeedbacksTemplates')->loadInstance($item['id']);
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @return array
     */
    public function getFeedbacks($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/Feedbacks')->getCollection();
        $tempCollection->addFieldToFilter('account_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $resultArray[] = Mage::getModel('M2ePro/Feedbacks')
                                        ->loadInstance($item['id']);
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @return array
     */
    public function getMessages($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/Messages')->getCollection();
        $tempCollection->addFieldToFilter('account_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $resultArray[] = Mage::getModel('M2ePro/Messages')
                                        ->loadInstance($item['id']);
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @return array
     */
    public function getEbayListings($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/EbayListings')->getCollection();
        $tempCollection->addFieldToFilter('account_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $resultArray[] = Mage::getModel('M2ePro/EbayListings')
                                        ->loadInstance($item['id']);
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    // ########################################

    public static function isSingleAccountMode()
    {
        return Mage::getModel('M2ePro/Accounts')->getCollection()->getSize() <= 1;
    }

    public function hasFeedbacksTemplates()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $size = Mage::getModel('M2ePro/FeedbacksTemplates')->getCollection()
                                                           ->addFieldToFilter('account_id', $this->getId())
                                                           ->getSize();
        return (bool)$size;
    }

    // ########################################

    public function getMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('mode');
    }

    public function isModeProduction()
    {
        return $this->getMode() == self::MODE_PRODUCTION;
    }

    public function isModeSandbox()
    {
        return $this->getMode() == self::MODE_SANDBOX;
    }

    //-------------

    public function getFeedbacksReceive()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('feedbacks_receive');
    }

    public function isFeedbacksReceive()
    {
        return $this->getFeedbacksReceive() == self::FEEDBACKS_RECEIVE_YES;
    }

    //-------------

    public function getFeedbacksAutoResponse()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('feedbacks_auto_response');
    }

    public function isFeedbacksAutoResponseDisabled()
    {
        return $this->getFeedbacksAutoResponse() == self::FEEDBACKS_AUTO_RESPONSE_NONE;
    }

    public function isFeedbacksAutoResponseCycled()
    {
        return $this->getFeedbacksAutoResponse() == self::FEEDBACKS_AUTO_RESPONSE_CYCLED;
    }

    public function isFeedbacksAutoResponseRandom()
    {
        return $this->getFeedbacksAutoResponse() == self::FEEDBACKS_AUTO_RESPONSE_RANDOM;
    }

    //-------------

    public function getFeedbacksAutoResponseOnlyPositive()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('feedbacks_auto_response_only_positive');
    }

    public function isFeedbacksAutoResponseOnlyPositive()
    {
        return $this->getFeedbacksAutoResponseOnlyPositive() == self::FEEDBACKS_AUTO_RESPONSE_ONLY_POSITIVE_YES;
    }

    //-------------

    public function getMessagesReceive()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('messages_receive');
    }

    public function isMessagesReceive()
    {
        return $this->getMessagesReceive() == self::MESSAGES_RECEIVE_YES;
    }

    //-------------

    public function getEbayListingsSynchronization()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('ebay_listings_synchronization');
    }

    public function isEbayListingsSynchronizationEnabled()
    {
        return $this->getEbayListingsSynchronization() == self::EBAY_LISTINGS_SYNCHRONIZATION_YES;
    }

    //-------------

    public function getOrdersListingsMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('orders_listings_mode');
    }

    public function isOrdersListingsModeEnabled()
    {
        return $this->getOrdersListingsMode() == self::ORDERS_LISTINGS_MODE_YES;
    }

    public function isOrdersListingsModeDisabled()
    {
        return $this->getOrdersListingsMode() == self::ORDERS_LISTINGS_MODE_NO;
    }

    //-------------

    public function getOrdersEbayMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('orders_ebay_mode');
    }

    public function isOrdersEbayModeEnabled()
    {
        return $this->getOrdersEbayMode() == self::ORDERS_EBAY_MODE_YES;
    }

    public function isOrdersEbayModeDisabled()
    {
        return $this->getOrdersEbayMode() == self::ORDERS_EBAY_MODE_NO;
    }

    //-------------

    public function getOrdersStatusCheckoutIncomplete()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('orders_status_checkout_incomplete');
    }

    public function isOrdersCreationOnCheckoutStatusCompleteEnabled()
    {
        return $this->getOrdersStatusCheckoutIncomplete() == self::ORDERS_CHECKOUT_MODE_COMPLETED;
    }

    public function isOrdersCreationOnCheckoutStatusAnyEnabled()
    {
        return $this->getOrdersStatusCheckoutIncomplete() == self::ORDERS_CHECKOUT_MODE_IGNORE;
    }

    //-------------

    public function getOrdersStatusPaymentCompleteMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('orders_status_payment_complete_mode');
    }

    public function isOrdersCreationOnPaymentStatusCompleteEnabled()
    {
        return $this->getOrdersStatusPaymentCompleteMode() == self::ORDERS_PAYMENT_MODE_COMPLETED;
    }

    public function isOrdersCreationOnPaymentStatusAnyEnabled()
    {
        return $this->getOrdersStatusPaymentCompleteMode() == self::ORDERS_PAYMENT_MODE_IGNORE;
    }

    //-------------

    public function getOrdersListingsStoreMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('orders_listings_store_mode');
    }

    public function isStoreFromListingEnabled()
    {
        return $this->getOrdersListingsStoreMode() == self::ORDERS_LISTINGS_STORE_MODE_NO;
    }

    //-------------

    public function getOrdersEbayCreateProduct()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('orders_ebay_create_product');
    }

    public function isEbayItemsImportEnabled()
    {
        return $this->getOrdersEbayCreateProduct() == self::ORDERS_EBAY_CREATE_PRODUCT_YES;
    }

    //-------------

    public function getOrdersStatusMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('orders_status_mode');
    }

    public function isOrdersStatusMappingDefault()
    {
        return $this->getOrdersStatusMode() == self::ORDERS_STATUS_MAPPING_DEFAULT;
    }

    public function isOrdersStatusMappingCustom()
    {
        return $this->getOrdersStatusMode() == self::ORDERS_STATUS_MAPPING_CUSTOM;
    }

    public function isInvoiceCreationEnabled()
    {
        if (!$this->isOrdersStatusMappingCustom()) {
            return true;
        }

        return $this->getData('orders_status_invoice') == self::ORDERS_INVOICE_MODE_YES;
    }

    public function isShipmentCreationEnabled()
    {
        if (!$this->isOrdersStatusMappingCustom()) {
            return true;
        }

        return $this->getData('orders_status_shipping') == self::ORDERS_SHIPMENT_MODE_YES;
    }

    //-------------

    public function getOrdersCustomerMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('orders_customer_mode');
    }

    public function isCustomerModeGuest()
    {
        return $this->getOrdersCustomerMode() == self::ORDERS_CUSTOMER_MODE_GUEST;
    }

    public function isCustomerModePredefined()
    {
        return $this->getOrdersCustomerMode() == self::ORDERS_CUSTOMER_MODE_EXIST;
    }

    public function isCustomerModeNew()
    {
        return $this->getOrdersCustomerMode() == self::ORDERS_CUSTOMER_MODE_NEW;
    }

    //-------------

    public function getOrdersCustomerNewSendNotifications()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('orders_customer_new_send_notifications');
    }

    public function isCustomerNewAccountNotificationEnabled()
    {
        return strpos($this->getOrdersCustomerNewSendNotifications(), 'a1') !== false;
    }

    public function isCustomerOrderNotificationEnabled()
    {
        return strpos($this->getOrdersCustomerNewSendNotifications(), 'o1') !== false;
    }

    public function isCustomerInvoiceNotificationEnabled()
    {
        return strpos($this->getOrdersCustomerNewSendNotifications(), 'i1') !== false;
    }

    public function isCustomerShipmentNotificationEnabled()
    {
        return strpos($this->getOrdersCustomerNewSendNotifications(), 's1') !== false;
    }

    //-------------

    public function getOrdersCustomerNewSubscribeNews()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('orders_customer_new_subscribe_news');
    }

    public function isCustomerSubscribeToNewsletterEnabled()
    {
        return $this->getOrdersCustomerNewSubscribeNews() == self::ORDERS_CUSTOMER_NEW_SUBSCRIBE_NEWS_YES;
    }

    // ########################################

    public function getTitle()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('title');
    }

    public function getServerHash()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('server_hash');
    }

    public function getTokenSession()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('token_session');
    }

    public function getTokenExpiredDate()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('token_expired_date');
    }

    //-------------

    public function getFeedbacksLastUsedId()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('feedbacks_last_used_id');
    }

    //-------------

    public function getOrdersCombinedMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('orders_combined_mode');
    }

    public function isOrdersCombinedEnabled()
    {
        return $this->getOrdersCombinedMode() == self::ORDERS_COMBINED_MODE_YES;
    }

    public function isOrdersCombinedDisabled()
    {
        return $this->getOrdersCombinedMode() == self::ORDERS_COMBINED_MODE_NO;
    }

    //-------------

    public function getOrderStatusOnCheckoutComplete()
    {
        if ($this->isOrdersStatusMappingDefault()) {
            return self::ORDERS_DEFAULT_STATUS_ON_CHECKOUT_COMPLETE;
        }

        return $this->getData('orders_status_checkout_completed')
                   ? $this->getData('orders_status_checkout_completed')
                   : self::ORDERS_DEFAULT_STATUS_ON_CHECKOUT_COMPLETE;
    }

    public function getOrderStatusOnPaymentComplete()
    {
        if ($this->isOrdersStatusMappingDefault()) {
            return self::ORDERS_DEFAULT_STATUS_ON_PAYMENT_COMPLETE;
        }

        return $this->getData('orders_status_payment_completed')
                   ? $this->getData('orders_status_payment_completed')
                   : self::ORDERS_DEFAULT_STATUS_ON_PAYMENT_COMPLETE;
    }

    public function getOrderStatusOnShippingComplete()
    {
        if ($this->isOrdersStatusMappingDefault()) {
            return self::ORDERS_DEFAULT_STATUS_ON_SHIPPING_COMPLETE;
        }

        return $this->getData('orders_status_shipping_completed')
                   ? $this->getData('orders_status_shipping_completed')
                   : self::ORDERS_DEFAULT_STATUS_ON_SHIPPING_COMPLETE;
    }

    //-------------

    public function getEbayStoreTitle()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('ebay_store_title');
    }

    public function getEbayStoreUrl()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('ebay_store_url');
    }

    public function getEbayStoreSubscriptionLevel()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('ebay_store_subscription_level');
    }

    public function getEbayStoreDescription()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('ebay_store_description');
    }

    public function getEbayStoreCategories()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $tableAccountStoreCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_accounts_store_categories');
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $dbSelect = $connRead->select()
                             ->from($tableAccountStoreCategories,'*')
                             ->where('`account_id` = ?',(int)$this->getId())
                             ->order(array('sorder ASC'));

        return $connRead->fetchAll($dbSelect);
    }

    public function buildEbayStoreCategoriesTreeRec($data, $rootId)
    {
        $children = array();

        foreach ($data as $node) {
            if ($node['parent_id'] == $rootId) {
                $children[] = array(
                    'id' => $node['category_id'],
                    'text' => $node['title'],
                    'allowDrop' => false,
                    'allowDrag' => false,
                    'children' => array()
                );
            }
        }

        foreach ($children as &$child) {
            $child['children'] = $this->buildEbayStoreCategoriesTreeRec($data,$child['id']);
        }

        return $children;
    }

    public function buildEbayStoreCategoriesTree()
    {
        return $this->buildEbayStoreCategoriesTreeRec($this->getEbayStoreCategories(), 0);
    }

    //-------------

    public function updateEbayStoreInfo()
    {
        // Get ebay store data
        //----------------------------
        $data = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                        ->processVirtual('account','get','store',
                                         array(),NULL,NULL,$this->getId());
        //----------------------------

        // Save ebay store information
        //----------------------------
        if (isset($data['data'])) {
            $dataForUpdate = array();
            foreach ($data['data'] as $key=>$value) {
                $dataForUpdate['ebay_store_'.$key] = $value;
            }
            $this->addData($dataForUpdate)->save();
        }
        //----------------------------

        // Save ebay store categories
        //----------------------------
        if (isset($data['categories'])) {
            $tableAccountStoreCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_accounts_store_categories');

            Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                    ->delete($tableAccountStoreCategories,array('account_id = ?'=>$this->getId()));

            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

            foreach ($data['categories'] as &$item) {
                $item['account_id'] = $this->getId();
                $connWrite->insertOnDuplicate($tableAccountStoreCategories, $item);
            }
        }
        //----------------------------
    }

    // ########################################
}