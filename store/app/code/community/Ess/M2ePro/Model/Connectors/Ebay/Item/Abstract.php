<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract extends Ess_M2ePro_Model_Connectors_Ebay_Abstract
{
    const STATUS_ERROR      = 1;
    const STATUS_WARNING    = 2;
    const STATUS_SUCCESS    = 3;

    /**
     * @var Ess_M2ePro_Model_Listings
     */
    protected $listing = NULL;
    protected $status = self::STATUS_SUCCESS;
    protected $logsActionId = NULL;

    /**
     * @var Ess_M2ePro_Model_ListingsLockItem
     */
    protected $lockItem = NULL;
    protected $isNeedRemoveLock = false;

    // ########################################
    
    public function __construct(array $params = array(), Ess_M2ePro_Model_Listings $listing)
    {
        $defaultParams = array(
            'status_changer' => Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_UNKNOWN
        );
        $params = array_merge($defaultParams, $params);

        if (isset($params['logs_action_id'])) {
            $this->logsActionId = (int)$params['logs_action_id'];
            unset($params['logs_action_id']);
        } else {
            $this->logsActionId = Mage::getModel('M2ePro/ListingsLogs')->getNextActionId();
        }

        $this->listing = $listing;
        
        parent::__construct($params,$this->listing->getListingTemplate()->getMarketplace(),
                            $this->listing->getListingTemplate()->getAccount(),NULL);
    }

    public function __destruct()
    {
        $this->checkUnlockListing();
    }

    // ########################################

    public function process()
    {
        $this->setStatus(self::STATUS_SUCCESS);

        if (!$this->validateNeedRequestSend()) {
            return array();
        }

        $this->updateOrLockListing();
        $result = parent::process();
        $this->checkUnlockListing();

        return $result;
    }

    //----------------------------------------

    abstract protected function validateNeedRequestSend();

    // ########################################

    protected function updateOrLockListing()
    {
        $this->lockItem = Mage::getModel('M2ePro/ListingsLockItem', array('id' => $this->listing->getId()));

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

    protected function addListingsProductsLogsMessage(Ess_M2ePro_Model_ListingsProducts $listingProduct,
                                                      array $message, $priority = Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM)
    {
        $this->addBaseListingsLogsMessage($listingProduct,$message,$priority);
    }

    protected function addListingsLogsMessage(array $message, $priority = Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM)
    {
        $this->addBaseListingsLogsMessage(NULL,$message,$priority);
    }

    private function addBaseListingsLogsMessage($listingProduct, array $message, $priority = Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM)
    {
        $action = $this->getListingsLogsCurrentAction();
        is_null($action) && $action = Ess_M2ePro_Model_ListingsLogs::ACTION_UNKNOWN;

        if (!isset($message[parent::MESSAGE_TEXT_KEY]) || $message[parent::MESSAGE_TEXT_KEY] == '') {
            return;
        }
        $text = $message[parent::MESSAGE_TEXT_KEY];

        if (!isset($message[parent::MESSAGE_TYPE_KEY]) || $message[parent::MESSAGE_TYPE_KEY] == '') {
            return;
        }
        $type = Ess_M2ePro_Model_ListingsLogs::TYPE_ERROR;
        switch ($message[parent::MESSAGE_TYPE_KEY]) {
            case parent::MESSAGE_TYPE_ERROR:
                    $type = Ess_M2ePro_Model_ListingsLogs::TYPE_ERROR;
                    $this->setStatus(self::STATUS_ERROR);
                break;
            case parent::MESSAGE_TYPE_WARNING:
                    $type = Ess_M2ePro_Model_ListingsLogs::TYPE_WARNING;
                    $this->setStatus(self::STATUS_WARNING);
                break;
            case parent::MESSAGE_TYPE_SUCCESS:
                    $type = Ess_M2ePro_Model_ListingsLogs::TYPE_SUCCESS;
                    $this->setStatus(self::STATUS_SUCCESS);
                break;
            case parent::MESSAGE_TYPE_NOTICE:
                    $type = Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE;
                    $this->setStatus(self::STATUS_SUCCESS);
                break;
            default:
                    $type = Ess_M2ePro_Model_ListingsLogs::TYPE_ERROR;
                    $this->setStatus(self::STATUS_ERROR);
                break;
        }

        $initiator = Ess_M2ePro_Model_LogsBase::INITIATOR_UNKNOWN;
        if ($this->params['status_changer'] == Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_UNKNOWN) {
            $initiator = Ess_M2ePro_Model_LogsBase::INITIATOR_UNKNOWN;
        } else if ($this->params['status_changer'] == Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_USER) {
            $initiator = Ess_M2ePro_Model_LogsBase::INITIATOR_USER;
        } else {
            $initiator = Ess_M2ePro_Model_LogsBase::INITIATOR_EXTENSION;
        }

        if (is_null($listingProduct)) {
            Mage::getModel('M2ePro/ListingsLogs')->addListingMessage($this->listing->getId() ,
                                                                     $initiator ,
                                                                     $this->logsActionId ,
                                                                     $action , $text, $type , $priority);
        } else {
            Mage::getModel('M2ePro/ListingsLogs')->addProductMessage($this->listing->getId() ,
                                                                     $listingProduct->getProductId() ,
                                                                     $initiator ,
                                                                     $this->logsActionId ,
                                                                     $action , $text, $type , $priority);
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
        if (!in_array($status,array(self::STATUS_ERROR, self::STATUS_WARNING, self::STATUS_SUCCESS))) {
            return;
        }

        if ($status == self::STATUS_ERROR) {
            $this->status = self::STATUS_ERROR;
            return;
        }

        if ($this->status == self::STATUS_ERROR) {
            return;
        }

        if ($status == self::STATUS_WARNING) {
            $this->status = self::STATUS_WARNING;
            return;
        }

        if ($this->status == self::STATUS_WARNING) {
            return;
        }

        $this->status = self::STATUS_SUCCESS;
    }

    public static function getMainStatus($statuses)
    {
        foreach (array(self::STATUS_ERROR, self::STATUS_WARNING) as $status) {
            if (in_array($status, $statuses)) {
                return $status;
            }
        }

        return self::STATUS_SUCCESS;
    }

    // ########################################
}