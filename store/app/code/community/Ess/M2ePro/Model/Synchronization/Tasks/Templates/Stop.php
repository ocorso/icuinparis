<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Templates_Stop extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 55;
    const PERCENTS_END = 70;
    const PERCENTS_INTERVAL = 15;

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
        $this->_profiler->addTitle('Stop Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Stop" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Stop" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
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

            if (!$this->isMeetStopRequirements($listingProduct)) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP,array());
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

            if (!$this->isMeetStopRequirements($listingProductVariationOption->getListingProduct())) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP,array());
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function isMeetStopRequirements(Ess_M2ePro_Model_ListingsProducts $listingProduct)
    {
        // Ebay available status
        //--------------------
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isStoppable()) {
            return false;
        }

        if ($this->_ebayActions->isExistProductAction($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP,array())) {
            return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        if ($listingProduct->getSynchronizationTemplate()->isStopStatusDisabled()) {
            if ($listingProduct->getMagentoProduct()->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                return true;
            }
        }

        if ($listingProduct->getSynchronizationTemplate()->isStopOutOfStock()) {
            if (!$listingProduct->getMagentoProduct()->getStockAvailability()) {
                return true;
            }
        }

        if ($listingProduct->getSynchronizationTemplate()->isStopWhenQtyHasValue()) {

            $productQty = (int)$listingProduct->getQty(true);

            $typeQty = (int)$listingProduct->getSynchronizationTemplate()->getStopWhenQtyHasValueType();
            $minQty = (int)$listingProduct->getSynchronizationTemplate()->getStopWhenQtyHasValueMin();
            $maxQty = (int)$listingProduct->getSynchronizationTemplate()->getStopWhenQtyHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::STOP_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }
        //--------------------

        return false;
    }

    //####################################
}