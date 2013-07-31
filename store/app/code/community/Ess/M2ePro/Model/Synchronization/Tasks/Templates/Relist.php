<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Templates_Relist extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 35;
    const PERCENTS_END = 55;
    const PERCENTS_INTERVAL = 20;

    private $_synchronizations = array();

    //####################################

    public function __construct()
    {
        parent::__construct();
        $this->_synchronizations = Mage::registry('synchTemplatesArray');
    }

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
        $this->_profiler->addTitle('Relist Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Relist" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Relist" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Relist immediatelied
        //---------------------
        $this->executeImmediately();
        //---------------------
        
        // Relist scheduled
        //---------------------
        $this->executeScheduled();
        //---------------------
    }

    //------------------------------------

    private function executeImmediately()
    {
        $this->immediatelyChangeEbayStatus();

        $this->_lockItem->setPercents(self::PERCENTS_START + 1*self::PERCENTS_INTERVAL/2);
        $this->_lockItem->activate();

        $this->immediatelyChangedProducts();
    }

    private function executeScheduled()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Synchronization templates with schedule');

        foreach ($this->_synchronizations as &$synchronization) {

            if (!$synchronization['instance']->isRelistMode()) {
                continue;
            }

            if (!$synchronization['instance']->isRelistShedule()) {
                continue;
            }

            if ($synchronization['instance']->getRelistSheduleType() ==
                Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_SCHEDULE_TYPE_WEEK) {

                if (!$synchronization['instance']->isRelistSheduleWeekDayNow() ||
                    !$synchronization['instance']->isRelistSheduleWeekTimeNow()) {
                    continue;
                }
            }

            $this->scheduledListings($synchronization['listings']);
            $this->_lockItem->activate();
        }
        
        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function immediatelyChangeEbayStatus()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Immediately when ebay status inactive');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();
        $attributesForProductsChanges[] = 'listing_product_status';
        //------------------------------------

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = Mage::getModel('M2ePro/ProductsChanges')->getChangedListingsProductsByAttributes($attributesForProductsChanges);
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        foreach ($changedListingsProducts as $changedListingProduct) {

            $tempNewValue = explode('_status_',$changedListingProduct['pc_value_new']);

            if (!is_array($tempNewValue) || count($tempNewValue) != 2) {
                continue;
            }

            $tempListingProductId = (int)str_replace('listing_product_','',$tempNewValue[0]);

            if ($tempListingProductId != (int)$changedListingProduct['id']) {
                continue;
            }

            $changedListingProduct['pc_value_new'] = (int)$tempNewValue[1];

            if ((int)$changedListingProduct['pc_value_new'] == Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED) {
                continue;
            }

            /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
            $listingProduct = Mage::getModel('M2ePro/ListingsProducts')->loadInstance($changedListingProduct['id']);

            if ($listingProduct->getSynchronizationTemplate()->isRelistShedule()) {
                continue;
            }

            if (!$this->isMeetRelistRequirements($listingProduct)) {
                continue;
            }

            if ($listingProduct->isRelistable()) {
                $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST,array());
            } else if ($listingProduct->isListable()) {
                $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_LIST,array());
            }
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function immediatelyChangedProducts()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Immediately when product was changed');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();
        $attributesForProductsChanges[] = 'product_instance';
        //------------------------------------

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = Mage::getModel('M2ePro/ProductsChanges')->getChangedListingsProductsByAttributes($attributesForProductsChanges);
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        foreach ($changedListingsProducts as $changedListingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
            $listingProduct = Mage::getModel('M2ePro/ListingsProducts')->loadInstance($changedListingProduct['id']);

            if ($listingProduct->getSynchronizationTemplate()->isRelistShedule()) {
                continue;
            }

            if (!$this->isMeetRelistRequirements($listingProduct)) {
                continue;
            }
            
            if ($listingProduct->isRelistable()) {
                $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST,array());
            } else if ($listingProduct->isListable()) {
                $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_LIST,array());
            }
        }
        //------------------------------------

        // Get changed listings products variations options
        //------------------------------------
        $changedListingsProductsVariationsOptions = Mage::getModel('M2ePro/ProductsChanges')->getChangedListingsProductsVariationsOptionsByAttributes($attributesForProductsChanges);
        //------------------------------------

        // Filter only needed listings products variations options
        //------------------------------------
        foreach ($changedListingsProductsVariationsOptions as $changedListingProductVariationOption) {

            /** @var $listingProductVariationOption Ess_M2ePro_Model_ListingsProductsVariationsOptions */
            $listingProductVariationOption = Mage::getModel('M2ePro/ListingsProductsVariationsOptions')->loadInstance($changedListingProductVariationOption['id']);

            if ($listingProductVariationOption->getSynchronizationTemplate()->isRelistShedule()) {
                continue;
            }

            if (!$this->isMeetRelistRequirements($listingProductVariationOption->getListingProduct())) {
                continue;
            }

            if ($listingProductVariationOption->getListingProduct()->isRelistable()) {
                $this->_ebayActions->setProduct($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST,array());
            } else if ($listingProductVariationOption->getListingProduct()->isListable()) {
                $this->_ebayActions->setProduct($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_LIST,array());
            }
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //------------------------------------

    private function scheduledListings(&$listings)
    {
        $listingsIds = array();

        foreach ($listings as &$listing) {

            /** @var $listing Ess_M2ePro_Model_Listings */
            
            if (!$listing->isSynchronizationNowRun()) {
                continue;
            }

            $listingsIds[] = (int)$listing->getId();
        }

        if (count($listingsIds) <= 0) {
            return;
        }

        $listingsProductsCollection = Mage::getModel('M2ePro/ListingsProducts')->getCollection();
        $listingsProductsCollection->getSelect()->where('`status` != '.(int)Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED);
        $listingsProductsCollection->getSelect()->where('`listing_id` IN ('.implode(',',$listingsIds).')');

        $listingsProductsArray = $listingsProductsCollection->toArray();

        if ((int)$listingsProductsArray['totalRecords'] <= 0) {
            return;
        }

        foreach ($listingsProductsArray['items'] as $listingProductArray) {

            /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
            $listingProduct = Mage::getModel('M2ePro/ListingsProducts')->loadInstance($listingProductArray['id']);

            if ($listingProduct->getSynchronizationTemplate()->getRelistSheduleType() ==
                Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_SCHEDULE_TYPE_THROUGH &&
                !$this->isScheduleThroughNow($listingProduct)) {
                continue;
            }

            if (!$this->isMeetRelistRequirements($listingProduct)) {
                continue;
            }

            if ($listingProduct->isRelistable()) {
                $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST,array());
            } else if ($listingProduct->isListable()) {
                $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_LIST,array());
            }
        }
    }

    //####################################

    private function isMeetRelistRequirements(Ess_M2ePro_Model_ListingsProducts $listingProduct)
    {
        // Ebay available status
        //--------------------
        if ($listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isListable() && !$listingProduct->isRelistable()) {
            return false;
        }

        if ($listingProduct->isRelistable() && $this->_ebayActions->isExistProductAction($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST,array())) {
            return false;
        } else if ($listingProduct->isListable() && $this->_ebayActions->isExistProductAction($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_LIST,array())) {
            return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
            return false;
        }

        if(!$listingProduct->getSynchronizationTemplate()->isRelistMode()) {
            return false;
        }

        if ($listingProduct->getSynchronizationTemplate()->isRelistFilterUserLock() &&
            $listingProduct->getStatusChanger() == Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_USER) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        if($listingProduct->getSynchronizationTemplate()->isRelistStatusEnabled()) {
            if ($listingProduct->getMagentoProduct()->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                return false;
            }
        }

        if($listingProduct->getSynchronizationTemplate()->isRelistIsInStock()) {
            if (!$listingProduct->getMagentoProduct()->getStockAvailability()) {
                return false;
            }
        }

        if($listingProduct->getSynchronizationTemplate()->isRelistWhenQtyHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getQty(true);

            $typeQty = (int)$listingProduct->getSynchronizationTemplate()->getRelistWhenQtyHasValueType();
            $minQty = (int)$listingProduct->getSynchronizationTemplate()->getRelistWhenQtyHasValueMin();
            $maxQty = (int)$listingProduct->getSynchronizationTemplate()->getRelistWhenQtyHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }
        //--------------------

        return true;
    }

    private function isScheduleThroughNow(Ess_M2ePro_Model_ListingsProducts $listingProduct)
    {
        $dateEnd = $listingProduct->getEbayEndDate();
        if (is_null($dateEnd) || $dateEnd == '') {
            return false;
        }

        $interval = 60;
        $metric = $listingProduct->getSynchronizationTemplate()->getRelistSheduleThroughMetric();
        $value = (int)$listingProduct->getSynchronizationTemplate()->getRelistSheduleThroughValue();

        if ($metric == Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_SCHEDULE_THROUGH_METRIC_DAYS) {
            $interval = 60*60*24;
        }
        if ($metric == Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_SCHEDULE_THROUGH_METRIC_HOURS) {
            $interval = 60*60;
        }
        if ($metric == Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_SCHEDULE_THROUGH_METRIC_MINUTES) {
            $interval = 60;
        }

        $interval = $interval*$value;
        $dateEnd = strtotime($dateEnd);
        
        if (Mage::helper('M2ePro')->getCurrentGmtDate(true) < $dateEnd + $interval) {
            return false;
        }

        return true;
    }

    //####################################
}