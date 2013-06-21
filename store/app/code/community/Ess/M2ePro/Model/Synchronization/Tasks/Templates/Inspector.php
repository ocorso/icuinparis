<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Templates_Inspector extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 15;
    const PERCENTS_END = 20;
    const PERCENTS_INTERVAL = 5;

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
        $this->_profiler->addTitle('Inspector Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Inspector" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Inspector" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Prepare last time for first time
        $this->checkAndPrepareLastTime();

        $lastTime = strtotime($this->getCheckLastTime());
        $interval = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/templates/inspector/','interval');
        if ($lastTime + $interval > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return;
        }

        $this->updateChanges();

        $this->setCheckLastTime(Mage::helper('M2ePro')->getCurrentGmtDate(true));
    }

    //####################################

    private function getCheckLastTime()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/templates/inspector/','last_time');
    }

    private function setCheckLastTime($time)
    {
        if ($time instanceof DateTime) {
            $time = (int)$time->format('U');
        }
        if (is_int($time)) {
            $time = strftime("%Y-%m-%d %H:%M:%S", $time);
        }
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/templates/inspector/','last_time',$time);
    }

    private function checkAndPrepareLastTime()
    {
        $lastTime = $this->getCheckLastTime();
        if (is_null($lastTime) || $lastTime == '') {
            $lastTime = new DateTime();
            $lastTime->modify("-1 year");
            $this->setCheckLastTime($lastTime);
        }
    }

    //####################################

    private function updateChanges()
    {
        foreach ($this->_synchronizations as &$synchronization) {

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listings */

                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $this->updateListingsChanges($listing);
            }
        }
    }

    private function updateListingsChanges(Ess_M2ePro_Model_Listings $listing)
    {
        $listingsProducts = $listing->getListingsProducts(true);

        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */

            $this->updateListingsProductsChanges($listingProduct);
        }
    }

    private function updateListingsProductsChanges(Ess_M2ePro_Model_ListingsProducts $listingProduct)
    {
        // STATUS changes
        //--------------------------------
        $tempStatus = $listingProduct->getMagentoProduct()->getStatus();

        if (!$listingProduct->isListed() && $tempStatus == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {

            $this->addProductChange($listingProduct->getProductId(),'status',
                                    Mage_Catalog_Model_Product_Status::STATUS_DISABLED,
                                    Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        }

        if ($listingProduct->isListed() && $tempStatus == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {

            $this->addProductChange($listingProduct->getProductId(),'status',
                                    Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
                                    Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
        }
        //--------------------------------

        // IN STOCK changes
        //--------------------------------
        $tempStockAvailability = $listingProduct->getMagentoProduct()->getStockAvailability();

        if (!$listingProduct->isListed() && $tempStockAvailability) {
            $this->addProductChange($listingProduct->getProductId(),'stock_availability',0,1);
        }

        if ($listingProduct->isListed() && !$tempStockAvailability) {
            $this->addProductChange($listingProduct->getProductId(),'stock_availability',1,0);
        }
        //--------------------------------

        // PRICE changes
        //--------------------------------
        if ($listingProduct->isListed()) {

            $this->addPriceProductChange($listingProduct,'getBuyItNowPrice',
                                         'getEbayBuyItNowPrice','getBuyItNowPriceSource');

            if ($listingProduct->isListingTypeAuction()) {

                $this->addPriceProductChange($listingProduct,'getReservePrice',
                                             'getEbayReservePrice','getReservePriceSource');
                $this->addPriceProductChange($listingProduct,'getStartPrice',
                                             'getEbayStartPrice','getStartPriceSource');
            }
        }
        //--------------------------------

        // QTY changes
        //--------------------------------
        if ($listingProduct->isListingTypeFixed()) {

            if ($listingProduct->isListed() ) {

                $productQty = $listingProduct->getQty();
                $ebayQty = $listingProduct->getEbayQty() - $listingProduct->getEbayQtySold();

                if ($productQty != $ebayQty) {

                    $attribute = '';

                    $src = $listingProduct->getSellingFormatTemplate()->getQtySource();
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_PRODUCT) {
                        $attribute = 'qty';
                    }
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_ATTRIBUTE) {
                        $attribute = $src['attribute'];
                    }

                    $attribute != '' && $this->addProductChange($listingProduct->getProductId(),$attribute,
                                                                $ebayQty,$productQty);
                }
            }
        }

        //--------------------------------
    }

    //####################################

    private function addPriceProductChange(Ess_M2ePro_Model_ListingsProducts $listingProduct,
                                           $currentMethod, $ebayMethod, $sourceMethod)
    {
        $tempPrice = $listingProduct->$currentMethod();

        if ($tempPrice != $listingProduct->$ebayMethod()) {

            $attribute = '';

            $src = $listingProduct->getSellingFormatTemplate()->$sourceMethod();
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                $attribute = 'price';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                $attribute = 'special_price';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                $attribute = $src['attribute'];
            }

            $attribute != '' && $this->addProductChange($listingProduct->getProductId(),$attribute,
                                                        $listingProduct->$ebayMethod(),$tempPrice);
        }
    }

    private function addProductChange($productId, $attribute, $oldValue, $newValue)
    {
        Mage::getModel('M2ePro/ProductsChanges')
                    ->updateAttribute( $productId,
                                       'product_instance',
                                       'any_old',
                                       'any_new' ,
                                        Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_SYNCHRONIZATION );

        Mage::getModel('M2ePro/ProductsChanges')
                                ->updateAttribute( $productId,
                                                   $attribute,
                                                   $oldValue,
                                                   $newValue,
                                                   Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_SYNCHRONIZATION );
    }

    //####################################
}