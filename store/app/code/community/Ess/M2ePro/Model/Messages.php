<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Messages extends Mage_Core_Model_Abstract
{
	/**
     *
     * @var Ess_M2ePro_Model_Accounts
     */
    private $_accountModel = NULL;

    // ########################################

	public function _construct()
	{
		parent::_construct();
		$this->_init('M2ePro/Messages');
	}

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_Messages
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Message does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $accountId
     * @return Ess_M2ePro_Model_Messages
     */
    public function loadByAccount($accountId)
    {
        $this->load($accountId,'account_id');

        if (is_null($this->getId())) {
             throw new Exception('Message does not exist. Probably it was deleted.');
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
             throw new Exception('Load instance first');
        }

        if (is_null($this->_accountModel)) {
            $this->_accountModel = Mage::getModel('M2ePro/Accounts')
                 ->loadInstance($this->getData('account_id'));
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
             throw new Exception('Load instance first');
        }

        $this->_accountModel = $instance;
    }

    // ########################################

    public function sendResponse($text)
    {
        $paramsConnector = array(
            'body' => $text,
            'parent_message_id' => $this->getData('message_id'),
            'recipient_id' => $this->getData('sender_name'),
            'item_id' => $this->getData('ebay_item_id')
        );
        Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                    ->processVirtual('message','add','entity',
                                     $paramsConnector,NULL,
                                     NULL,$this->getAccount()->getId());
    }

    // ########################################

    public static function haveNewMessages()
    {
        $showMessagesNotification = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/messages/notification/', 'mode');
        if (!$showMessagesNotification) {
            return false;
        }

        $lastCheckDate = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/messages/notification/', 'last_check');

        if (is_null($lastCheckDate)) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/messages/notification/', 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate());
            return false;
        }

        $newMessagesReceived = Mage::getModel('M2ePro/Messages')->getCollection()
                                                                ->addFieldToFilter('message_date', array('gt' => $lastCheckDate))
                                                                ->getSize();

        if ($newMessagesReceived) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/messages/notification/', 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate());
        }

        return $newMessagesReceived;
    }

    // ########################################

    public static function receiveMessages(Ess_M2ePro_Model_Accounts $account, array $paramsConnector = array())
    {
        // Create connector
        //-----------------------
        $messages = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                                ->processVirtual('message','get','memberList',
                                                 $paramsConnector,'messages',
                                                 NULL,$account->getId());
        is_null($messages) && $messages = array();
        //-----------------------

        // Get new messages
        //-----------------------
        $countNewMessages = 0;
        foreach ($messages as $message) {
            $dbMessage = array(
                'account_id'      => $account->getId(),
                'ebay_item_id'    => $message['item_id'],
                'ebay_item_title' => $message['item_title'],
                'sender_name'     => $message['sender_name'],
                'message_id'      => $message['id'],
                'message_subject' => $message['subject'],
                'message_text'    => $message['body'],
                'message_date'    => $message['creation_date'],
                'message_type'    => $message['type']
            );

            if (isset($message['responses'])) {
                $dbMessage['message_responses'] = json_encode($message['responses'], JSON_FORCE_OBJECT);
            }

            $existMessage = Mage::getModel('M2ePro/Messages')
                                    ->getCollection()
                                    ->addFieldToFilter('message_id', $message['id'])
                                    ->toArray();

            $tempModel = Mage::getModel('M2ePro/Messages');

            if (count($existMessage['items']) != 0) {
                $tempModel->setId($existMessage['items'][0]['id']);
            } else {
                $countNewMessages++;
            }

            $tempModel->addData($dbMessage)->save();
        }
        //-----------------------

        return array(
            'new'   => $countNewMessages,
            'total' => count($messages)
        );
    }

    // ########################################
}