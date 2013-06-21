<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Model_Synchronization_Tasks_Orders extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    protected $_orderFixedItemTransactionInfo = array();
    protected $_orderAuctionItemTransactionInfo = array();

    protected $_iterationNumber = 0;
    protected $_percentInIteration = 0;

    protected $_configGroup = '/synchronization/settings/orders/';

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //$this->createEbayActions();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        //$this->executeEbayActions();
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_ORDERS);

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Orders Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__, 'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__('Orders Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "Orders Synchronization" is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "Orders Synchronization" is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Get accounts with enabled order synchronization
        //---------------------------
        $accounts = Mage::getModel('M2ePro/Accounts')->getCollection()
                                                     ->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Accounts::ORDERS_MODE_YES)
                                                     ->getItems();

        if (!count($accounts)) {
            return;
        }
        //---------------------------

        // Processing each account
        //---------------------------
        $accountIteration = 1;
        $percentsForAccount = self::PERCENTS_INTERVAL / count($accounts);

        $lastSuccessTime = $this->_getEbayCheckSinceTime();

        foreach ($accounts as $account) {

            $lastSuccessTime = $this->processAccount($account, $percentsForAccount);

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration);
            $this->_lockItem->activate();

            $accountIteration++;
        }
        //---------------------------
        $this->_profiler->saveTimePoint(__METHOD__);

        if ($lastSuccessTime) {
            $this->_setEbayCheckSinceTime($lastSuccessTime);
        }
    }

    //####################################

    protected function processAccount(Ess_M2ePro_Model_Accounts $account, $percentsForAccount)
    {
        $this->_profiler->addEol();
        $this->_profiler->addTitle('Starting account "'.$account->getData('title').'"');

        $this->_profiler->addEol();
        $this->_profiler->addTimePoint(__METHOD__.'get'.$account->getData('id'),'Get orders from eBay');

        $tempString = str_replace('%acc%',$account->getTitle(),Mage::helper('M2ePro')->__('Task "Orders Synchronization" for eBay account: "%acc%" is started. Please wait...'));
        $this->_lockItem->setStatus($tempString);

        $currentPercent = $this->_lockItem->getPercents();

        // Get since time
        //---------------------------
        $lastSinceTime = $this->_getEbayCheckSinceTime();
        //---------------------------

        // Get orders from ebay
        //---------------------------
        $request = array(
            'account' => $account->getServerHash(),
            'last_update' => $lastSinceTime,
        );

        $response = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')->processVirtual('sales', 'get', 'list', $request);

        $ebayOrders = array();
        $lastSuccessTime = $lastSinceTime;

        if (isset($response['sales']) && isset($response['updated_to'])) {
            $ebayOrders = $response['sales'];
            $lastSuccessTime = $response['updated_to'];
        }

        if (count($ebayOrders) <= 0) {
            return $lastSuccessTime;
        }
        //---------------------------

        $currentPercent = $currentPercent + $percentsForAccount * 0.15;
        $this->_lockItem->setPercents($currentPercent);
        $this->_lockItem->activate();

        $this->_profiler->saveTimePoint(__METHOD__.'get'.$account->getData('id'));

        $this->_profiler->addTitle('Total count orders received from eBay: '.count($ebayOrders));
        $this->_profiler->addTimePoint(__METHOD__.'process'.$account['id'],'Processing received orders from eBay');

        $tempString = str_replace('%acc%',$account['title'],Mage::helper('M2ePro')->__('Task "Orders Synchronization" for eBay account: "%acc%" is in data processing state. Please wait...'));
        $this->_lockItem->setStatus($tempString);

        // Save eBay orders
        //---------------------------
        $orders = array();

        foreach ($ebayOrders as $ebayOrderData) {
            /** @var $ebayOrder Ess_M2ePro_Model_Orders_Ebay_Order */
            $ebayOrder = Mage::getModel('M2ePro/Orders_Ebay_Order');
            $ebayOrder->setAccount($account);
            $ebayOrder->initialize($ebayOrderData);

            $result = $ebayOrder->process();

            if ($result) {
                $orders[] = $result;
            }
        }
        //---------------------------

        if (!count($orders)) {
            return $lastSuccessTime;
        }

        $currentPercent = $currentPercent + $percentsForAccount * 0.05;
        $this->_lockItem->setPercents($currentPercent);
        $this->_lockItem->activate();

        $this->_profiler->saveTimePoint(__METHOD__.'process'.$account->getData('id'));

        $this->_profiler->addEol();
        $this->_profiler->addTimePoint(__METHOD__.'magento_orders_process'.$account['id'],'Creating magento orders');

        $tempString = str_replace('%acc%',$account['title'],Mage::helper('M2ePro')->__('Task "Orders Synchronization" for eBay account: "%acc%" is in order creation state.. Please wait...'));
        $this->_lockItem->setStatus($tempString);

        // Create magento orders
        //---------------------------
        $magentoOrders = 0;
        $paymentTransactions = 0;
        $invoices = 0;
        $shipments = 0;

        $percentPerOrder = floor(($percentsForAccount - $currentPercent) / count($orders));

        foreach ($orders as $order) {
            /** @var $order Ess_M2ePro_Model_Orders_Order */
            $order->createMagentoOrder() && $magentoOrders++;
            $order->createPaymentTransactionForMagentoOrder() && $paymentTransactions++;
            $order->createInvoiceForMagentoOrder() && $invoices++;
            $order->createShipmentForMagentoOrder() && $shipments++;

            $currentPercent = $currentPercent + $percentPerOrder;
            $this->_lockItem->setPercents($currentPercent);
            $this->_lockItem->activate();
        }
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'magento_orders_process'.$account->getData('id'));

        $this->_profiler->addTitle('Total count magento orders created: ' . $magentoOrders);
        $this->_profiler->addTitle('Total count payment transactions created: ' . $paymentTransactions);
        $this->_profiler->addTitle('Total count invoices created: ' . $invoices);
        $this->_profiler->addTitle('Total count shipments created: ' . $shipments);

        $this->_profiler->addEol();
        $this->_profiler->addTitle('End account "'.$account->getData('title').'"');

        return $lastSuccessTime;
    }

    //####################################

    protected function _setEbayCheckSinceTime($sinceTime)
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue($this->_configGroup, 'since_time', Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($sinceTime));
    }

    protected function _getEbayCheckSinceTime()
    {
        $lastSinceTime = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($this->_configGroup, 'since_time');

        if (is_null($lastSinceTime)) {
            $lastSinceTime = new DateTime();
            $lastSinceTime->modify('-1 year');
        } else {
            $lastSinceTime = new DateTime($lastSinceTime);
        }
        //------------------------

        // Get min should for synch
        //------------------------
        $minShouldTime = new DateTime();
        $minShouldTime->modify('-1 month');
        //------------------------

        // Prepare last since time
        //------------------------
        if ((int)$lastSinceTime->format('U') < (int)$minShouldTime->format('U')) {
            $lastSinceTime = new DateTime();
            //if (Mage::helper('M2ePro/Module')->isInstalledM2eLastVersion()) {
                $lastSinceTime->modify('-1 hour');
            //} else {
            //    $lastSinceTime->modify("-10 days");
            //}
            $this->_setEbayCheckSinceTime($lastSinceTime);
        }

        return Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($lastSinceTime);
    }

    //####################################
}