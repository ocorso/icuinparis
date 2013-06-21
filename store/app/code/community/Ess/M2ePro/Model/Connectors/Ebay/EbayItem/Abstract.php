<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connectors_Ebay_EbayItem_Abstract extends Ess_M2ePro_Model_Connectors_Ebay_Abstract
{
    /**
     * @var Ess_M2ePro_Model_EbayListings
     */
    protected $ebayListing = NULL;
    protected $status = Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_SUCCESS;
    protected $logsActionId = NULL;

    /**
     * @var Ess_M2ePro_Model_ListingsLockItem
     */
    protected $lockItem = NULL;
    protected $isNeedRemoveLock = false;

    // ########################################
    
    public function __construct(array $params = array(), Ess_M2ePro_Model_EbayListings $ebayListing)
    {
        $defaultParams = array();
        $params = array_merge($defaultParams, $params);

        if (isset($params['logs_action_id'])) {
            $this->logsActionId = (int)$params['logs_action_id'];
            unset($params['logs_action_id']);
        } else {
            $this->logsActionId = Mage::getModel('M2ePro/EbayListingsLogs')->getNextActionId();
        }
        
        $this->ebayListing = $ebayListing;
        
        parent::__construct($params,$this->ebayListing->getMarketplace(),
                            $this->ebayListing->getAccount(),NULL);
    }

    public function __destruct()
    {
        $this->checkUnlockListing();
    }

    // ########################################

    public function process()
    {
        $this->setStatus(Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_SUCCESS);

        if (!$this->validateNeedRequestSend()) {
            return array();
        }

        $this->updateOrLockListing();
        $result = parent::process();
        $this->checkUnlockListing();

        foreach ($this->messages as $message) {
            $priorityMessage = Ess_M2ePro_Model_EbayListingsLogs::PRIORITY_MEDIUM;
            if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                $priorityMessage = Ess_M2ePro_Model_EbayListingsLogs::PRIORITY_HIGH;
            }
            $this->addProductsLogsMessage($this->ebayListing, $message, $priorityMessage);
        }

        return $result;
    }

    //----------------------------------------

    abstract protected function validateNeedRequestSend();

    // ########################################

    protected function updateOrLockListing()
    {
        $this->lockItem = Mage::getModel('M2ePro/ListingsLockItem', array('id' => 'listingsEbay'));

        if (!$this->lockItem->isExist()) {
            $this->lockItem->create();
            $this->isNeedRemoveLock = true;
        }

        $this->lockItem->activate();
    }

    protected function checkUnlockListing()
    {
        if (!is_null($this->lockItem) && $this->lockItem->isExist()) {
            $this->isNeedRemoveLock && $this->lockItem->remove();
        }
        $this->isNeedRemoveLock = false;
    }

    // ########################################

    protected function addProductsLogsMessage(Ess_M2ePro_Model_EbayListings $ebayListing,
                                              array $message, $priority = Ess_M2ePro_Model_EbayListingsLogs::PRIORITY_MEDIUM)
    {
        $this->addBaseLogsMessage($ebayListing,$message,$priority);
    }

    protected function addLogsMessage(array $message, $priority = Ess_M2ePro_Model_EbayListingsLogs::PRIORITY_MEDIUM)
    {
        $this->addBaseLogsMessage(NULL,$message,$priority);
    }

    private function addBaseLogsMessage($ebayListing, array $message, $priority = Ess_M2ePro_Model_EbayListingsLogs::PRIORITY_MEDIUM)
    {
        $action = $this->getListingsLogsCurrentAction();
        is_null($action) && $action = Ess_M2ePro_Model_EbayListingsLogs::ACTION_UNKNOWN;

        if (!isset($message[parent::MESSAGE_TEXT_KEY]) || $message[parent::MESSAGE_TEXT_KEY] == '') {
            return;
        }
        $text = $message[parent::MESSAGE_TEXT_KEY];

        if (!isset($message[parent::MESSAGE_TYPE_KEY]) || $message[parent::MESSAGE_TYPE_KEY] == '') {
            return;
        }
        $type = Ess_M2ePro_Model_EbayListingsLogs::TYPE_ERROR;
        switch ($message[parent::MESSAGE_TYPE_KEY]) {
            case parent::MESSAGE_TYPE_ERROR:
                    $type = Ess_M2ePro_Model_EbayListingsLogs::TYPE_ERROR;
                    $this->setStatus(Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR);
                break;
            case parent::MESSAGE_TYPE_WARNING:
                    $type = Ess_M2ePro_Model_EbayListingsLogs::TYPE_WARNING;
                    $this->setStatus(Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_WARNING);
                break;
            case parent::MESSAGE_TYPE_SUCCESS:
                    $type = Ess_M2ePro_Model_EbayListingsLogs::TYPE_SUCCESS;
                    $this->setStatus(Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_SUCCESS);
                break;
            case parent::MESSAGE_TYPE_NOTICE:
                    $type = Ess_M2ePro_Model_EbayListingsLogs::TYPE_NOTICE;
                    $this->setStatus(Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_SUCCESS);
                break;
            default:
                    $type = Ess_M2ePro_Model_EbayListingsLogs::TYPE_ERROR;
                    $this->setStatus(Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR);
                break;
        }

        $initiator = Ess_M2ePro_Model_LogsBase::INITIATOR_USER;

        if (is_null($ebayListing)) {
            Mage::getModel('M2ePro/EbayListingsLogs')->addGlobalMessage($initiator , $this->logsActionId , $action , $text , $type , $priority);
        } else {
            Mage::getModel('M2ePro/EbayListingsLogs')->addProductMessage($ebayListing->getId() ,
                                                                         $initiator ,
                                                                         $this->logsActionId ,
                                                                         $action , $text , $type , $priority);
        }
    }

    abstract protected function getListingsLogsCurrentAction();

    // ########################################

    public function getStatus()
    {
        return $this->status;
    }

    protected function setStatus($status)
    {
        if (!in_array($status,array(Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR, Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_WARNING, Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_SUCCESS))) {
            return;
        }

        if ($status == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR) {
            $this->status = Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR;
            return;
        }

        if ($this->status == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR) {
            return;
        }

        if ($status == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_WARNING) {
            $this->status = Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_WARNING;
            return;
        }

        if ($this->status == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_WARNING) {
            return;
        }

        $this->status = Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_SUCCESS;
    }

    // ########################################
}