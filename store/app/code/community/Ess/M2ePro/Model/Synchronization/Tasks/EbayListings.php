<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Model_Synchronization_Tasks_EbayListings extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    const EBAY_STATUS_ACTIVE = 'Active';
    const EBAY_STATUS_ENDED = 'Ended';
    const EBAY_STATUS_COMPLETED = 'Completed';

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
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_EBAY_LISTINGS);

        $this->_profiler->addEol();
        $this->_profiler->addTitle('3rd Party Listings Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__, 'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__('3rd Party Listings Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "3rd Party Listings Synchronization" is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "3rd Party Listings Synchronization" is finished. Please wait...'));

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
        $this->_profiler->addTimePoint(__METHOD__,'Get and process items from ebay');

        // Get separate all accounts
        //---------------------------
        $accounts = Mage::getModel('M2ePro/Accounts')->getCollection()
                                ->addFieldToFilter("ebay_listings_synchronization", Ess_M2ePro_Model_Accounts::EBAY_LISTINGS_SYNCHRONIZATION_YES)
                                ->getItems();
        if (count($accounts) <= 0) {
            return;
        }
        //---------------------------

        // Processing each account
        //---------------------------
        $accountIteration = 1;
        $percentsForAccount = self::PERCENTS_INTERVAL / count($accounts);

        foreach ($accounts as $account) {

            $this->processAccount($account, $percentsForAccount);

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration);
            $this->_lockItem->activate();
            $accountIteration++;
        }
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function processAccount(Ess_M2ePro_Model_Accounts $account, $percentsForAccount)
    {
        $this->_profiler->addTitle('Starting account "'.$account->getData('title').'"');

        $this->_profiler->addTimePoint(__METHOD__.'get'.$account->getData('id'),'Get items from eBay');

        $tempString = str_replace('%acc%',$account->getData('title'),Mage::helper('M2ePro')->__('Task "3rd Party Listings Synchronization" for eBay account: "%acc%" is started. Please wait...'));
        $this->_lockItem->setStatus($tempString);

        $currentPercent = $this->_lockItem->getPercents();

        $currentPercent = $currentPercent + $percentsForAccount * 0.05;
        $this->_lockItem->setPercents($currentPercent);
        $this->_lockItem->activate();

        // Get since time
        //---------------------------
        $sinceTime = $account->getData('ebay_listings_last_synchronization');
        //---------------------------

        // Get all items from eBay
        //---------------------------
        if (is_null($sinceTime)) {

            $tempSinceTime = new DateTime();
            $tempSinceTime->modify("-118 days");
            $tempSinceTime = strftime("%Y-%m-%d %H:%M:%S", (int)$tempSinceTime->format('U'));

            $responseData = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                                ->processVirtual('item','get','all',
                                                 array('since_time'=>$tempSinceTime),NULL,
                                                 NULL,$account->getId(),NULL);
        } else {

            $tempSinceTime = $this->prepareSinceTime($sinceTime);

            $responseData = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                                ->processVirtual('item','get','changes',
                                                 array('since_time'=>$tempSinceTime),NULL,
                                                 NULL,$account->getId(),NULL);
        }

        $currentPercent = $currentPercent + $percentsForAccount * 0.15;
        $this->_lockItem->setPercents($currentPercent);
        $this->_lockItem->activate();

        $items = array();
        $tempToTime = $sinceTime;

        if (isset($responseData['items']) && isset($responseData['to_time'])) {
            $items = (array)$responseData['items'];
            if (is_array($responseData['to_time']) && isset($responseData['to_time'][0])) {
                $tempToTime = (string)$responseData['to_time'][0];
            } else {
                $tempToTime = (string)$responseData['to_time'];
            }
        }
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'get'.$account->getData('id'));

        $this->_profiler->addTitle('Total count items from eBay: '.count($items));

        $this->_profiler->addTimePoint(__METHOD__.'prepare'.$account['id'],'Processing received items from eBay');
        $tempString = str_replace('%acc%',$account['title'],Mage::helper('M2ePro')->__('Task "3rd Party Listings Synchronization" for eBay account: "%acc%" is in data processing state. Please wait...'));
        $this->_lockItem->setStatus($tempString);

        // Progress bar
        //---------------------------
        $numberOfListings = count($items) > 0 ? count($items) : 1;
        $numberOfListingsToChangePercent = 1;
        $percentPerListing = ($percentsForAccount - $currentPercent) / $numberOfListings;

        if ($percentPerListing < 1) {
            $numberOfListingsToChangePercent = floor(1 / $percentPerListing);
            $percentPerListing = 1;
        }
        //---------------------------

        // Save ebay listings
        //---------------------------
        $newItems = 0;
        $totalItems = 0;
        foreach ($items as $item) {

            $totalItems++;
            $canChangePercent = $totalItems % $numberOfListingsToChangePercent == 0;

            if ($canChangePercent) {
                $currentPercent = $currentPercent + $percentPerListing;
                $this->_lockItem->setPercents($currentPercent);
                $this->_lockItem->activate();
            }

            if ($this->_isOldPartOfExtensionRecord($item)) {
                continue;
            }

            if ($this->_isOldPartOfExistRecord($item)) {
                continue;
            }

            $item = $this->_prepareForInsert($item,$account);
            Mage::getModel('M2ePro/EbayListings')->setData($item)->save();

            $newItems++;
        }
        //---------------------------

        // Update since time
        //---------------------------
        $account->addData(array('ebay_listings_last_synchronization'=>$tempToTime))->save();
        //---------------------------

        $this->_profiler->addTitle('Count not related with M2ePro items: '.$newItems);

        $this->_profiler->saveTimePoint(__METHOD__.'prepare'.$account->getData('id'));
        $this->_profiler->addEol();
    }

    //####################################

    private function _isOldPartOfExistRecord($singleNonM2eProduct)
    {
        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                                     ->select()
                                     ->from(Mage::getResourceModel('M2ePro/EbayListings')->getMainTable(),new Zend_Db_Expr('COUNT(*)'))
                                     ->where("`ebay_old_items` LIKE '%{$singleNonM2eProduct['id']}%'");

        $items = Mage::getModel('Core/Mysql4_Config')->getReadConnection()->fetchOne($dbSelect);
        
        if ($items === false || (int)$items <= 0) {
            return false;
        }

        return true;
    }

    private function _isOldPartOfExtensionRecord($singleNonM2eProduct)
    {
        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                                     ->select()
                                     ->from(Mage::getResourceModel('M2ePro/EbayItems')->getMainTable(),new Zend_Db_Expr('COUNT(*)'))
                                     ->where('`item_id` = ?', $singleNonM2eProduct['id']);

        $items = Mage::getModel('Core/Mysql4_Config')->getReadConnection()->fetchOne($dbSelect);

        if ($items === false || (int)$items <= 0) {
            return false;
        }

        return true;
    }

    private function prepareSinceTime($lastSinceTime)
    {
        // Get last since time
        //------------------------
        if (is_null($lastSinceTime)) {
            $lastSinceTime = new DateTime();
            $lastSinceTime->modify("-1 year");
        } else {
            $lastSinceTime = new DateTime($lastSinceTime);
        }
        //------------------------

        // Get min shold for synch
        //------------------------
        $minSholdTime = new DateTime();
        $minSholdTime->modify("-1 month");
        //------------------------

        // Prepare last since time
        //------------------------
        if ((int)$lastSinceTime->format('U') < (int)$minSholdTime->format('U')) {

            $lastSinceTime = new DateTime();
            $lastSinceTime->modify("-10 days");
        }
        //------------------------

        return strftime("%Y-%m-%d %H:%M:%S", (int)$lastSinceTime->format('U'));
    }

    private function _prepareForInsert($item, Ess_M2ePro_Model_Accounts $account)
    {
        $result = array(
            'id' => Mage::getModel('M2ePro/EbayListings')->load($item['id'], 'ebay_item')->getId(),
            'account_id' => (int)$account->getId(),
            'marketplace_id' => (int)Mage::getModel('M2ePro/Marketplaces')->load($item['marketplace'],"code")->getId(),
            'ebay_item' =>  (double)$item['id'],
            'ebay_price' => (float)$item['currentPrice'],
            'ebay_currency' => (string)$item['currency'],
            'ebay_title' => (string)$item['title'],
            'ebay_qty' => (int)$item['quantity'],
            'ebay_qty_sold' => (int)$item['quantitySold'],
            'ebay_bids' => (int)$item['bidCount'],
            'ebay_start_date' => (string)Mage::helper('M2ePro')->getDate($item['startTime']),
            'ebay_end_date' => (string)Mage::helper('M2ePro')->getDate($item['endTime'])
        );

        if ($item['listingType'] == Ess_M2ePro_Model_SellingFormatTemplates::EBAY_LISTING_TYPE_AUCTION) {
            $result['ebay_qty'] = 1;
        }

        if (($item['listingStatus'] == self::EBAY_STATUS_COMPLETED ||
             $item['listingStatus'] == self::EBAY_STATUS_ENDED) &&
             $result['ebay_qty'] == $result['ebay_qty_sold']) {

            $result['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_SOLD;

        } else if ($item['listingStatus'] == self::EBAY_STATUS_COMPLETED) {

            $result['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED;

        } else if ($item['listingStatus'] == self::EBAY_STATUS_ENDED) {

            $result['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_FINISHED;

        } else if ($item['listingStatus'] == self::EBAY_STATUS_ACTIVE) {

            $result['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED;

        }

        return $result;
    }

    //####################################
}