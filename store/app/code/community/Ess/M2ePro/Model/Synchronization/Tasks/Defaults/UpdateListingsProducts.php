<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Defaults_UpdateListingsProducts extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 5;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 95;

    const EBAY_STATUS_ACTIVE = 'Active';
    const EBAY_STATUS_ENDED = 'Ended';
    const EBAY_STATUS_COMPLETED = 'Completed';

    private $tempToTime = NULL;

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
        $this->_profiler->addTitle('Update Listings Products');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "Update Listings Products" is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "Update Listings Products" is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Prepare since time for first time
        $this->checkAndPrepareSinceTime();

        // Get all changed listings products items
        $changedListingsProducts = $this->getChangedListingsProducts();

        $this->_profiler->addTimePoint(__METHOD__,'Update listings products');

        // Update listings products
        $this->updateListingsProducts($changedListingsProducts);

        // Update listings products variations
        $this->updateListingsProductsVariations($changedListingsProducts);

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function getEbayCheckSinceTime()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/defaults/update_listings_products/','since_time');
    }

    private function setEbayCheckSinceTime($time)
    {
        if ($time instanceof DateTime) {
            $time = (int)$time->format('U');
        }
        if (is_int($time)) {
            $time = strftime("%Y-%m-%d %H:%M:%S", $time);
        }
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/defaults/update_listings_products/','since_time',$time);
    }

    private function checkAndPrepareSinceTime()
    {
        // Get last since time
        //------------------------
        $lastSinceTime = $this->getEbayCheckSinceTime();

        if (is_null($lastSinceTime) || $lastSinceTime == '') {
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
            $this->setEbayCheckSinceTime($lastSinceTime);
        }
        //------------------------
    }

    //####################################

    private function getChangedListingsProducts()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Get & prepared all changes from eBay');

        // Get Time last update From. For all account same
        //---------------------------
        $sinceTime = $this->getEbayCheckSinceTime();
        //---------------------------

        // For each account get item that changed into eBay
        //---------------------------
        $ebayAccounts = Mage::getModel('M2ePro/Accounts')
                                ->getCollection()
                                ->toArray();
        if ((int)$ebayAccounts['totalRecords'] == 0) {
            return array();
        }
        //---------------------------

        // Get changes for each account
        //---------------------------
        $changedListingsProducts = array();

        $accountIteration = 1;
        $percentsForAccount = (5*(self::PERCENTS_INTERVAL/6))/(int)$ebayAccounts['totalRecords'];
        
        foreach ($ebayAccounts['items'] as $account) {

            $changedListingsProductsForAccount = $this->getChangedListingsProductsForAccount($account,$sinceTime);
            $changedListingsProducts = array_merge($changedListingsProducts,$changedListingsProductsForAccount);
    
            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration);
            $this->_lockItem->activate();
            $accountIteration++;
        }
        //---------------------------

        // Update since time for next times
        //---------------------------
        if (!is_string($this->tempToTime) || $this->tempToTime == '') {
            $this->tempToTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        }
        $this->setEbayCheckSinceTime($this->tempToTime);
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
        
        return $changedListingsProducts;
    }

    private function getChangedListingsProductsForAccount($account, $sinceTime)
    {
        $this->_profiler->addTitle('Starting account "'.$account['title'].'"');

        $this->_profiler->addTimePoint(__METHOD__.'get'.$account['id'],'Get changes from eBay');

        $tempString = str_replace('%acc%',$account['title'],Mage::helper('M2ePro')->__('Task "Update Listings Products" for eBay account: "%acc%" is started. Please wait...'));
        $this->_lockItem->setStatus($tempString);

        // Get all changes on eBay for account
        //---------------------------
        $responseData = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                                ->processVirtual('item','get','changes',
                                                 array('since_time'=>$sinceTime),NULL,
                                                 NULL,$account['id'],NULL);

        $changedItems = array();

        if (isset($responseData['items']) && isset($responseData['to_time'])) {
            $changedItems = (array)$responseData['items'];
            $this->tempToTime = (string)$responseData['to_time'];
        } else {
            is_null($this->tempToTime) && $this->tempToTime = (string)$sinceTime;
        }
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'get'.$account['id']);

        $this->_profiler->addTitle('Total count changes from ebay: '.count($changedItems));

        $this->_profiler->addTimePoint(__METHOD__.'prepare'.$account['id'],'Processing received changes from eBay');

        $tempString = str_replace('%acc%',$account['title'],Mage::helper('M2ePro')->__('Task "Update Listings Products" for eBay account: "%acc%" is in data processing state. Please wait...'));
        $this->_lockItem->setStatus($tempString);

        // Save changed listings products
        //---------------------------
        $changedListingsProducts = array();
        foreach ($changedItems as $changeItem) {

            // Check exist listing product
            //--------------------------
            $tempListingProductModel = Mage::getModel('M2ePro/ListingsProducts')
                                                 ->getInstanceByEbayItem($changeItem['id']);

            if ($tempListingProductModel === false) {
                continue;
            }

            // Listing product don't listed
            if ($tempListingProductModel->getStatus() != Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED) {
                continue;
            }
            //--------------------------

            // Get prepared listings products
            //--------------------------
            $changedListingsProducts[] = $this->prepareChangedListingsProducts($tempListingProductModel,$changeItem);
            //--------------------------
        }
        //---------------------------

        $this->_profiler->addTitle('Count related with M2ePro changes: '.count($changedListingsProducts));

        $this->_profiler->saveTimePoint(__METHOD__.'prepare'.$account['id']);
        $this->_profiler->addEol();

        return $changedListingsProducts;
    }

    private function prepareChangedListingsProducts(Ess_M2ePro_Model_ListingsProducts $tempListingProductModel, $ebayChange)
    {
        // Prepare ebay changes values
        //--------------------------
        $tempEbayChanges = array();

        if ($tempListingProductModel->isListingTypeAuction()) {
            $tempEbayChanges['ebay_start_price'] = (float)$ebayChange['currentPrice'] < 0 ? 0 : (float)$ebayChange['currentPrice'];
        }
        if ($tempListingProductModel->isListingTypeFixed()) {
            $tempEbayChanges['ebay_buyitnow_price'] = (float)$ebayChange['currentPrice'] < 0 ? 0 : (float)$ebayChange['currentPrice'];
        }
        
        $tempEbayChanges['ebay_qty'] = (int)$ebayChange['quantity'] < 0 ? 0 : (int)$ebayChange['quantity'];
        $tempEbayChanges['ebay_qty_sold'] = (int)$ebayChange['quantitySold'] < 0 ? 0 : (int)$ebayChange['quantitySold'];

        if ($tempListingProductModel->isListingTypeAuction()) {
            $tempEbayChanges['ebay_qty'] = 1;
            $tempEbayChanges['ebay_bids'] = (int)$ebayChange['bidCount'] < 0 ? 0 : (int)$ebayChange['bidCount'];
        }
        
        $tempEbayChanges['ebay_start_date'] = Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($ebayChange['startTime']);
        $tempEbayChanges['ebay_end_date'] = Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($ebayChange['endTime']);
        
        if (($ebayChange['listingStatus'] == self::EBAY_STATUS_COMPLETED || $ebayChange['listingStatus'] == self::EBAY_STATUS_ENDED) &&
            $tempEbayChanges['ebay_qty'] == $tempEbayChanges['ebay_qty_sold']) {

            $tempEbayChanges['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_SOLD;

        } else if ($ebayChange['listingStatus'] == self::EBAY_STATUS_COMPLETED) {

            $tempEbayChanges['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED;

        } else if ($ebayChange['listingStatus'] == self::EBAY_STATUS_ENDED) {

            $tempEbayChanges['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_FINISHED;

        } else if ($ebayChange['listingStatus'] == self::EBAY_STATUS_ACTIVE) {

            $tempEbayChanges['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED;

        }

        if ($tempEbayChanges['status'] != $tempListingProductModel->getStatus()) {

            $tempEbayChanges['status_changer'] = Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_EBAY;
            
            Mage::getModel('M2ePro/ProductsChanges')
                    ->updateAttribute( $tempListingProductModel->getProductId(),
                                       'listing_product_status',
                                       'listing_product_'.$tempListingProductModel->getId().'_status_'.$tempListingProductModel->getStatus(),
                                       'listing_product_'.$tempListingProductModel->getId().'_status_'.$tempEbayChanges['status'] ,
                                       Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_SYNCHRONIZATION );
        }
        //--------------------------

        // Create changed listings products
        //--------------------------
        $changedListingsProducts = array(
            'ebay_item_id' => $ebayChange['id'],
            'listing_product' => array(
                'instance' => $tempListingProductModel,
                'changes' => $tempEbayChanges
            ),
            'listings_products_variations' => array()
        );
        //--------------------------

        // Cancel when have not ebay variations
        //--------------------------
        if (!isset($ebayChange['variations']) || is_null($ebayChange['variations'])) {
            return $changedListingsProducts;
        }
        //--------------------------

        // Get listings products variations
        //-----------------------
        $tempVariations = $tempListingProductModel->getListingsProductsVariations(true);
        if (count($tempVariations) == 0) {
            return $changedListingsProducts;
        }
        //-----------------------

        // Get listings products variations with options
        //-----------------------
        $tempVariationsWithOptions = array();

        foreach ($tempVariations as $variation) {

            $options = $variation->getListingsProductsVariationsOptions(true);

            if (count($options) == 0) {
                continue;
            }

            $tempVariationsWithOptions[] = array(
                'variation' => $variation,
                'options' => $options
            );
        }

        if (count($tempVariationsWithOptions) == 0) {
            return $changedListingsProducts;
        }
        //-----------------------

        // Search our variations for ebay variations
        //--------------------------
        foreach ($ebayChange['variations'] as $ebayVariation) {

            // Find our variation
            //--------------------------
            foreach ($tempVariationsWithOptions as $M2eProVariation) {

                $equalVariation = true;

                foreach ($M2eProVariation['options'] as $M2eProOptionValue) {

                    $haveOption = false;

                    foreach ($ebayVariation['specifics'] as $ebayOptionKey=>$ebayOptionValue) {

                        if ($M2eProOptionValue->getData('attribute') == $ebayOptionKey && $M2eProOptionValue->getData('option') == $ebayOptionValue) {
                            $haveOption = true;
                            break;
                        }
                    }

                    if ($haveOption === false) {
                        $equalVariation = false;
                        break;
                    }
                }

                if ($equalVariation === true && count($M2eProVariation['options']) == count($ebayVariation['specifics'])) {

                    // Prepare ebay changes values
                    //--------------------------
                    $tempEbayChanges = array();

                    $tempEbayChanges['ebay_price'] = (float)$ebayVariation['price'] < 0 ? 0 : (float)$ebayVariation['price'];
                    $tempEbayChanges['ebay_qty'] = (int)$ebayVariation['quantity'] < 0 ? 0 : (int)$ebayVariation['quantity'];
                    $tempEbayChanges['ebay_qty_sold'] = (int)$ebayVariation['quantitySold'] < 0 ? 0 : (int)$ebayVariation['quantitySold'];

                    if ($tempEbayChanges['ebay_qty'] <= $tempEbayChanges['ebay_qty_sold']) {
                        $tempEbayChanges['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_SOLD;
                    }
                    if ($tempEbayChanges['ebay_qty'] <= 0) {
                        $tempEbayChanges['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED;
                    }
                    //--------------------------

                    // Add changed variation
                    //--------------------------
                    $changedListingsProducts['listings_products_variations'][] = array(
                        'instance' => $M2eProVariation,
                        'changes' => $tempEbayChanges
                    );
                    //--------------------------

                    break;
                }
            }
            //--------------------------
        }

        return $changedListingsProducts;
    }

    //####################################

    private function updateListingsProducts(&$changedListingsProducts)
    {
        foreach ($changedListingsProducts as $listingProduct) {

            // Get separate data
            //--------------------------
            $listingProductModel = $listingProduct['listing_product']['instance'];
            $listingProductChanges = $listingProduct['listing_product']['changes'];
            //--------------------------

            // Save updated data
            //--------------------------
            $listingProductModel->addData($listingProductChanges)->save();
            //--------------------------

            // Update variations status
            //--------------------------
            $tempVariations = $listingProductModel->getListingsProductsVariations(true);
            foreach ($tempVariations as $variation) {

                if ($listingProductModel->getData('status') != Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED &&
                    $listingProductModel->getData('status') != Ess_M2ePro_Model_ListingsProducts::STATUS_FINISHED) {
                    $dataForUpdate['status'] = $listingProductModel->getData('status');
                    $variation->addData($dataForUpdate)->save();
                }

                if ($listingProductModel->getData('status') == Ess_M2ePro_Model_ListingsProducts::STATUS_FINISHED &&
                    $variation->getData('status') == Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED) {
                    $dataForUpdate['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_FINISHED;
                    $variation->addData($dataForUpdate)->save();
                }
            }
            //--------------------------
        }
    }

    private function updateListingsProductsVariations(&$changedListingsProducts)
    {
        foreach ($changedListingsProducts as $listingProduct) {

            foreach ($listingProduct['listings_products_variations'] as $listingProductVariation) {

                // Get separate data
                //--------------------------
                $listingProductVariationModel = $listingProductVariation['instance']['variation'];
                $listingProductVariationChanges = $listingProductVariation['changes'];
                //--------------------------

                // Save updated data
                //--------------------------
                $listingProductVariationModel->addData($listingProductVariationChanges)->save();
                //--------------------------
            }
        }
    }

    //####################################
}