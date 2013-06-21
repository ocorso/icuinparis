<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Feedbacks extends Mage_Core_Model_Abstract
{
    const TYPE_NEUTRAL  = 'Neutral';
    const TYPE_POSITIVE = 'Positive';
    const TYPE_NEGATIVE = 'Negative';

    const ROLE_BUYER  = 'Buyer';
    const ROLE_SELLER = 'Seller';

    // ########################################

	/**
     *
     * @var Ess_M2ePro_Model_Accounts
     */
    private $_accountModel = NULL;

    // ########################################

	public function _construct()
	{
		parent::_construct();
		$this->_init('M2ePro/Feedbacks');
	}

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_Feedbacks
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Feedback does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $accountId
     * @return Ess_M2ePro_Model_Feedbacks
     */
    public function loadByAccount($accountId)
    {
        $this->load($accountId,'account_id');

        if (is_null($this->getId())) {
             throw new Exception('Feedback does not exist. Probably it was deleted.');
        }

        return $this;
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

        return false;
    }

    /**
     * @return bool
     */
    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->_accountModel = NULL;

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Accounts
     */
    public function getAccount()
    {
        if (is_null($this->getId())) {
             throw new Exception('Feedback does not exist. Probably it was deleted.');
        }

        if (is_null($this->_accountModel)) {
            $this->_accountModel = Mage::getModel('M2ePro/Accounts')->loadInstance($this->getData('account_id'));
        }

        return $this->_accountModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_Accounts $instance
     * @return void
     */
    public function setAccount(Ess_M2ePro_Model_Accounts $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Feedback does not exist. Probably it was deleted.');
        }

        $this->_accountModel = $instance;
    }

    // ########################################

    public function isNeutral()
    {
        return $this->getData('buyer_feedback_type') == self::TYPE_NEUTRAL;
    }

    public function isNegative()
    {
        return $this->getData('buyer_feedback_type') == self::TYPE_NEGATIVE;
    }

    public function isPositive()
    {
        return $this->getData('buyer_feedback_type') == self::TYPE_POSITIVE;
    }

    // ########################################

    public function sendResponse($text, $type = self::TYPE_POSITIVE)
    {
        $paramsConnector = array(
            'item_id'        => $this->getData('ebay_item_id'),
            'transaction_id' => $this->getData('ebay_transaction_id'),
            'text'           => $text,
            'type'           => $type,
            'target_user'    => $this->getData('buyer_name')
        );
        Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                    ->processVirtual('feedback','add','entity',
                                     $paramsConnector,NULL,
                                     NULL,$this->getAccount()->getId());
    }

    // ########################################

    public static function haveNewFeedbacks($onlyNegative = false)
    {
        $showFeedbacksNotification = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/feedbacks/notification/', 'mode');
        if (!$showFeedbacksNotification) {
            return false;
        }

        $lastCheckDate = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/feedbacks/notification/', 'last_check');

        if (is_null($lastCheckDate)) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/feedbacks/notification/', 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate());
            return false;
        }

        $feedbacksCollection = Mage::getModel('M2ePro/Feedbacks')->getCollection()
                                                                 ->addFieldToFilter('buyer_feedback_date', array('gt' => $lastCheckDate));

        if ($onlyNegative) {
            $feedbacksCollection->addFieldToFilter('buyer_feedback_type', Ess_M2ePro_Model_Feedbacks::TYPE_NEGATIVE);
        }

        $newFeedbacksReceived = $feedbacksCollection->getSize();

        if ($newFeedbacksReceived) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/feedbacks/notification/', 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate());
        }
        
        return $newFeedbacksReceived;
    }

    public static function receiveFeedbacks(Ess_M2ePro_Model_Accounts $account, array $paramsConnector = array())
    {
        // Create connector
        //-----------------------
        $feedbacks = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                                ->processVirtual('feedback','get','entity',
                                                 $paramsConnector,'feedbacks',
                                                 NULL,$account->getId());
        is_null($feedbacks) && $feedbacks = array();
        //-----------------------

        // Get and update feedbacks
        //-----------------------
        $countNewFeedbacks = 0;
        foreach ($feedbacks as $feedback) {

            $dbFeedback = array(
                'account_id' => $account->getId(),
                'ebay_item_id' => $feedback['item_id'],
                'ebay_transaction_id' => $feedback['transaction_id']
            );

            if ($feedback['item_title'] != '') {
                $dbFeedback['ebay_item_title'] = $feedback['item_title'];
            }

            if ($feedback['from_role'] == self::ROLE_BUYER) {
                $dbFeedback['buyer_name'] = $feedback['user_sender'];
                $dbFeedback['buyer_feedback_id'] = $feedback['id'];
                $dbFeedback['buyer_feedback_text'] = $feedback['info']['text'];
                $dbFeedback['buyer_feedback_date'] = $feedback['info']['date'];
                $dbFeedback['buyer_feedback_type'] = $feedback['info']['type'];
            } else {
                $dbFeedback['seller_feedback_id'] = $feedback['id'];
                $dbFeedback['seller_feedback_text'] = $feedback['info']['text'];
                $dbFeedback['seller_feedback_date'] = $feedback['info']['date'];
                $dbFeedback['seller_feedback_type'] = $feedback['info']['type'];
            }

            $existFeedback = Mage::getModel('M2ePro/Feedbacks')->getCollection()
                                                               ->addFieldToFilter('account_id', $account->getId())
                                                               ->addFieldToFilter('ebay_item_id', $feedback['item_id'])
                                                               ->addFieldToFilter('ebay_transaction_id', $feedback['transaction_id'])
                                                               ->getFirstItem();

            if ($existFeedback->getId()) {
                if ($feedback['from_role'] == self::ROLE_BUYER && !$existFeedback->getData('buyer_feedback_id')) {
                    $countNewFeedbacks++;
                }
                if ($feedback['from_role'] == self::ROLE_SELLER && !$existFeedback->getData('seller_feedback_id')) {
                    $countNewFeedbacks++;
                }
                $existFeedback->addData($dbFeedback);
            } else {
                $existFeedback->setData($dbFeedback);
                $countNewFeedbacks++;
            }

            $existFeedback->save();
        }
        //-----------------------

        return array(
            'total' => count($feedbacks),
            'new' => $countNewFeedbacks
        );
    }

    public static function getLastNoResponsed($daysAgo = 30)
    {
        $tableFeedbacks = Mage::getResourceModel('M2ePro/Feedbacks')->getMainTable();
        $tableAccounts  = Mage::getResourceModel('M2ePro/Accounts')->getMainTable();
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $dbSelect = $connRead->select()
                             ->from(array('f'=>$tableFeedbacks),'*')
                             ->join(array('a'=>$tableAccounts),'`a`.`id` = `f`.`account_id`',array())
                             ->where('`f`.`seller_feedback_id` = 0 OR `f`.`seller_feedback_id` IS NULL')
                             ->where('`f`.`buyer_feedback_date` > DATE_SUB(NOW(), INTERVAL ? DAY)',(int)$daysAgo)
                             ->order(array('buyer_feedback_date ASC'));

        $feedbacksArray =  $connRead->fetchAll($dbSelect);

        $feedbacksModels = array();
        foreach ($feedbacksArray as $feedbackItem) {
            $feedbacksModels[] = Mage::getModel('M2ePro/Feedbacks')->loadInstance($feedbackItem['id']);
        }

        return $feedbacksModels;
    }

    // ########################################
}