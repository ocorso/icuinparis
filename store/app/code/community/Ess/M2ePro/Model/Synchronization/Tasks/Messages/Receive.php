<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Messages_Receive extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Receive Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Receive" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Receive" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Prepare MySQL data
        //-----------------------
        $tableMessages = Mage::getResourceModel('M2ePro/Messages')->getMainTable();
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        //-----------------------

        // Get all accounts
        //-----------------------
        $accounts = Mage::getModel('M2ePro/Accounts')->getCollection()->getItems();

        $tempAccounts = array();
        foreach ($accounts as $account) {
            if (!$account->isMessagesReceive()) {
                continue;
            }
            $tempAccounts[] = $account;
        }
        $accounts = $tempAccounts;

        if (count($accounts) == 0) {
            return;
        }
        //-----------------------

        // Process accounts
        //-----------------------
        $iteration = 1;
        $percentsForStep = self::PERCENTS_INTERVAL / count($accounts);

        foreach ($accounts as $account) {

            if ($iteration != 1) {
                $this->_profiler->addEol();
            }

            $this->_profiler->addTitle('Starting account "'.$account->getData('title').'"');
            $this->_profiler->addTimePoint(__METHOD__.'get'.$account->getId(),'Get messages from eBay');

            $tempString = str_replace('%acc%',$account->getData('title'),Mage::helper('M2ePro')->__('The "Receive" action for eBay account: "%acc%" is started. Please wait...'));
            $this->_lockItem->setStatus($tempString);

            //-----------------------
            $dbSelect = $connRead->select()
                                 ->from($tableMessages,new Zend_Db_Expr('MAX(`message_date`)'))
                                 ->where('`account_id` = ?',(int)$account->getId());
            $maxDate = $connRead->fetchOne($dbSelect);
            if (is_null($maxDate)) {
                $tempDate = new DateTime('-30 days');
                $maxDate = $tempDate->format('Y-m-d H:i:s');
            }
            //-----------------------

            // Update messages
            //-----------------------
            $paramsConnector = array('since_time' => $maxDate);

            $resultReceive = Mage::getModel('M2ePro/Messages')->receiveMessages($account,$paramsConnector);

            $this->_profiler->addTitle('Total received messages from eBay: '.$resultReceive['total']);
            $this->_profiler->addTitle('Total only new messages from eBay: '.$resultReceive['new']);
            //-----------------------

            $this->_profiler->saveTimePoint(__METHOD__.'get'.$account->getId());

            $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep);
            $this->_lockItem->activate();
            $iteration++;
        }
        //------------------------
    }

    //####################################
}