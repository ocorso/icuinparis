<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Templates_Revise extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 20;
    const PERCENTS_END = 35;
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
        $this->_profiler->addTitle('Revise Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Revise" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Revise" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $this->executeQtyChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 1*self::PERCENTS_INTERVAL/11);
        $this->_lockItem->activate();

        $this->executePriceChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 2*self::PERCENTS_INTERVAL/11);
        $this->_lockItem->activate();

        //-------------------------

        $this->executeVariationStatusChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 3*self::PERCENTS_INTERVAL/11);
        $this->_lockItem->activate();

        $this->executeVariationQtyIsInStockChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 4*self::PERCENTS_INTERVAL/11);
        $this->_lockItem->activate();

        $this->executeSpecialPriceIntervalChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 5*self::PERCENTS_INTERVAL/11);
        $this->_lockItem->activate();

        //-------------------------

        $this->executeTitleChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 6*self::PERCENTS_INTERVAL/11);
        $this->_lockItem->activate();

        $this->executeSubTitleChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 7*self::PERCENTS_INTERVAL/11);
        $this->_lockItem->activate();

        $this->executeDescriptionChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 8*self::PERCENTS_INTERVAL/11);
        $this->_lockItem->activate();

        //-------------------------

        $this->executeSellingFormatTemplatesChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 9*self::PERCENTS_INTERVAL/11);
        $this->_lockItem->activate();

        $this->executeDescriptionsTemplatesChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 10*self::PERCENTS_INTERVAL/11);
        $this->_lockItem->activate();

        $this->executeListingsTemplatesChanged();
    }

    //####################################

    private function executeQtyChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update quantity');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();

        foreach ($this->_synchronizations as &$synchronization) {

            if (!$synchronization['instance']->isReviseWhenChangeQty()) {
                continue;
            }

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listings */
                
                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $src = $listing->getSellingFormatTemplate()->getQtySource();
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_PRODUCT) {
                    $attributesForProductsChanges[] = 'qty';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_ATTRIBUTE) {
                    $attributesForProductsChanges[] = $src['attribute'];
                }
            }
        }

        $attributesForProductsChanges = array_unique($attributesForProductsChanges);
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

            if (!$listingProduct->isListed()) {
                continue;
            }

            if ($this->_ebayActions->isExistProductAction($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('qty'=>true)))) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            $attributeNeeded = '';

            $src = $listingProduct->getSellingFormatTemplate()->getQtySource();
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_PRODUCT) {
                $attributeNeeded = 'qty';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_ATTRIBUTE) {
                $attributeNeeded = $src['attribute'];
            }

            if ($attributeNeeded != $changedListingProduct['pc_attribute']) {
                continue;
            }

            if (!$listingProduct->getSynchronizationTemplate()->isReviseWhenChangeQty()) {
                continue;
            }

            if ($listingProduct->getQty() <= 0) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('qty'=>true)));
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

            if (!$listingProductVariationOption->getListingProduct()->isListed()) {
                continue;
            }

            if ($this->_ebayActions->isExistProductAction($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('variations'=>true)))) {
                continue;
            }

            if (!$listingProductVariationOption->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            $attributeNeeded = '';

            $src = $listingProductVariationOption->getSellingFormatTemplate()->getQtySource();
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_PRODUCT) {
                $attributeNeeded = 'qty';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_ATTRIBUTE) {
                $attributeNeeded = $src['attribute'];
            }

            if ($attributeNeeded != $changedListingProductVariationOption['pc_attribute']) {
                continue;
            }

            if (!$listingProductVariationOption->getSynchronizationTemplate()->isReviseWhenChangeQty()) {
                continue;
            }

            if ($listingProductVariationOption->getListingProduct()->getQty() <= 0) {
                continue;
            }

            if (!$listingProductVariationOption->getListingProduct()->isRevisable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('variations'=>true)));
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executePriceChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update price');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();

        foreach ($this->_synchronizations as &$synchronization) {

            if (!$synchronization['instance']->isReviseWhenChangePrice()) {
                continue;
            }

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listings */
                
                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $src = $listing->getSellingFormatTemplate()->getStartPriceSource();
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                    $attributesForProductsChanges[] = 'price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                    $attributesForProductsChanges[] = 'special_price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                    $attributesForProductsChanges[] = $src['attribute'];
                }

                $src = $listing->getSellingFormatTemplate()->getReservePriceSource();
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                    $attributesForProductsChanges[] = 'price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                    $attributesForProductsChanges[] = 'special_price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                    $attributesForProductsChanges[] = $src['attribute'];
                }

                $src = $listing->getSellingFormatTemplate()->getBuyItNowPriceSource();
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                    $attributesForProductsChanges[] = 'price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                    $attributesForProductsChanges[] = 'special_price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                    $attributesForProductsChanges[] = $src['attribute'];
                }
            }
        }

        $attributesForProductsChanges = array_unique($attributesForProductsChanges);
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

            if (!$listingProduct->isListed()) {
                continue;
            }

            if ($this->_ebayActions->isExistProductAction($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('price'=>true,'variations'=>true)))) {
                continue;
            }

            if ($listingProduct->getSellingFormatTemplate()->isPriceVariationModeChildren() &&
                ($listingProduct->getMagentoProduct()->isConfigurableType() ||
                 $listingProduct->getMagentoProduct()->isBundleType())) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            $attributeNeeded = '';

            $src = $listingProduct->getSellingFormatTemplate()->getStartPriceSource();
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                $attributeNeeded = 'price';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                $attributeNeeded = 'special_price';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                $attributeNeeded = $src['attribute'];
            }

            if ($attributeNeeded != $changedListingProduct['pc_attribute'] &&
                !($attributeNeeded == 'special_price' && $changedListingProduct['pc_attribute'] == 'price')) {

                $src = $listingProduct->getSellingFormatTemplate()->getReservePriceSource();
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                    $attributeNeeded = 'price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                    $attributeNeeded = 'special_price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                    $attributeNeeded = $src['attribute'];
                }

                if ($attributeNeeded != $changedListingProduct['pc_attribute'] &&
                    !($attributeNeeded == 'special_price' && $changedListingProduct['pc_attribute'] == 'price')) {

                    $src = $listingProduct->getSellingFormatTemplate()->getBuyItNowPriceSource();
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                        $attributeNeeded = 'price';
                    }
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                        $attributeNeeded = 'special_price';
                    }
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                        $attributeNeeded = $src['attribute'];
                    }

                    if ($attributeNeeded != $changedListingProduct['pc_attribute'] &&
                        !($attributeNeeded == 'special_price' && $changedListingProduct['pc_attribute'] == 'price')) {
                        continue;
                    }
                }
            }

            if (!$listingProduct->getSynchronizationTemplate()->isReviseWhenChangePrice()) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('price'=>true,'variations'=>true)));
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

            if (!$listingProductVariationOption->getListingProduct()->isListed()) {
                continue;
            }

            if ($this->_ebayActions->isExistProductAction($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('variations'=>true)))) {
                continue;
            }

            if ($listingProductVariationOption->getSellingFormatTemplate()->isPriceVariationModeParent() &&
                ($listingProductVariationOption->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
                 $listingProductVariationOption->getListingProduct()->getMagentoProduct()->isBundleType())) {
                continue;
            }

            if (!$listingProductVariationOption->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            $attributeNeeded = '';

            $src = $listingProductVariationOption->getSellingFormatTemplate()->getStartPriceSource();
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                $attributeNeeded = 'price';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                $attributeNeeded = 'special_price';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                $attributeNeeded = $src['attribute'];
            }

            if ($attributeNeeded != $changedListingProductVariationOption['pc_attribute'] &&
                !($attributeNeeded == 'special_price' && $changedListingProductVariationOption['pc_attribute'] == 'price')) {

                $src = $listingProductVariationOption->getSellingFormatTemplate()->getReservePriceSource();
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                    $attributeNeeded = 'price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                    $attributeNeeded = 'special_price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                    $attributeNeeded = $src['attribute'];
                }

                if ($attributeNeeded != $changedListingProductVariationOption['pc_attribute'] &&
                    !($attributeNeeded == 'special_price' && $changedListingProductVariationOption['pc_attribute'] == 'price')) {

                    $src = $listingProductVariationOption->getSellingFormatTemplate()->getBuyItNowPriceSource();
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                        $attributeNeeded = 'price';
                    }
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                        $attributeNeeded = 'special_price';
                    }
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                        $attributeNeeded = $src['attribute'];
                    }

                    if ($attributeNeeded != $changedListingProductVariationOption['pc_attribute'] &&
                        !($attributeNeeded == 'special_price' && $changedListingProductVariationOption['pc_attribute'] == 'price')) {
                        continue;
                    }
                }
            }

            if (!$listingProductVariationOption->getSynchronizationTemplate()->isReviseWhenChangePrice()) {
                continue;
            }

            if (!$listingProductVariationOption->getListingProduct()->isRevisable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('variations'=>true)));
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################
    
    private function executeVariationStatusChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update variation status');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();
        $attributesForProductsChanges[] = 'status';
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

            if (!$listingProductVariationOption->getListingProduct()->isListed()) {
                continue;
            }

            if ($this->_ebayActions->isExistProductAction($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('variations'=>true)))) {
                continue;
            }

            if (!$listingProductVariationOption->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!$listingProductVariationOption->getSynchronizationTemplate()->isReviseWhenChangeQty()) {
                continue;
            }

            if ($listingProductVariationOption->getListingProduct()->getQty() <= 0) {
                continue;
            }

            if (!$listingProductVariationOption->getListingProduct()->isRevisable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('variations'=>true)));
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeVariationQtyIsInStockChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update variation stock availability');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();
        $attributesForProductsChanges[] = 'stock_availability';
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

            if (!$listingProductVariationOption->getListingProduct()->isListed()) {
                continue;
            }

            if ($this->_ebayActions->isExistProductAction($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('variations'=>true)))) {
                continue;
            }
            
            if (!$listingProductVariationOption->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!$listingProductVariationOption->getSynchronizationTemplate()->isReviseWhenChangeQty()) {
                continue;
            }

            if ($listingProductVariationOption->getListingProduct()->getQty() <= 0) {
                continue;
            }

            if (!$listingProductVariationOption->getListingProduct()->isRevisable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('variations'=>true)));
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeSpecialPriceIntervalChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update special price interval');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();
        $attributesForProductsChanges[] = 'special_price_from_date';
        $attributesForProductsChanges[] = 'special_price_to_date';
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

            if (!$listingProduct->isListed()) {
                continue;
            }

            if ($this->_ebayActions->isExistProductAction($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('price'=>true,'variations'=>true)))) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            $attributeNeeded = '';

            $src = $listingProduct->getSellingFormatTemplate()->getStartPriceSource();
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                $attributeNeeded = 'price';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                $attributeNeeded = 'special_price';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                $attributeNeeded = $src['attribute'];
            }

            if ($attributeNeeded != 'special_price') {

                $src = $listingProduct->getSellingFormatTemplate()->getReservePriceSource();
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                    $attributeNeeded = 'price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                    $attributeNeeded = 'special_price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                    $attributeNeeded = $src['attribute'];
                }

                if ($attributeNeeded != 'special_price') {

                    $src = $listingProduct->getSellingFormatTemplate()->getBuyItNowPriceSource();
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                        $attributeNeeded = 'price';
                    }
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                        $attributeNeeded = 'special_price';
                    }
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                        $attributeNeeded = $src['attribute'];
                    }

                    if ($attributeNeeded != 'special_price') {
                        continue;
                    }
                }
            }

            if (!$listingProduct->getSynchronizationTemplate()->isReviseWhenChangePrice()) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('price'=>true,'variations'=>true)));
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

            if (!$listingProductVariationOption->getListingProduct()->isListed()) {
                continue;
            }

            if ($this->_ebayActions->isExistProductAction($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('variations'=>true)))) {
                continue;
            }

            if (!$listingProductVariationOption->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions() &&
                !$listingProductVariationOption->getListingProduct()->getMagentoProduct()->isGroupedType()) {
                continue;
            }

            if (!$listingProductVariationOption->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            $attributeNeeded = '';

            $src = $listingProductVariationOption->getSellingFormatTemplate()->getStartPriceSource();
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                $attributeNeeded = 'price';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                $attributeNeeded = 'special_price';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                $attributeNeeded = $src['attribute'];
            }

            if ($attributeNeeded != 'special_price') {

                $src = $listingProductVariationOption->getSellingFormatTemplate()->getReservePriceSource();
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                    $attributeNeeded = 'price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                    $attributeNeeded = 'special_price';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                    $attributeNeeded = $src['attribute'];
                }

                if ($attributeNeeded != 'special_price') {

                    $src = $listingProductVariationOption->getSellingFormatTemplate()->getBuyItNowPriceSource();
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT) {
                        $attributeNeeded = 'price';
                    }
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL) {
                        $attributeNeeded = 'special_price';
                    }
                    if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                        $attributeNeeded = $src['attribute'];
                    }

                    if ($attributeNeeded != 'special_price') {
                        continue;
                    }
                }
            }

            if (!$listingProductVariationOption->getSynchronizationTemplate()->isReviseWhenChangePrice()) {
                continue;
            }

            if (!$listingProductVariationOption->getListingProduct()->isRevisable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('variations'=>true)));
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeTitleChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update title');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();

        foreach ($this->_synchronizations as &$synchronization) {

            if (!$synchronization['instance']->isReviseWhenChangeTitle()) {
                continue;
            }

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listings */
                
                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $attributesForProductsChanges = array_merge($attributesForProductsChanges,$listing->getDescriptionTemplate()->getTitleAttributes());
            }
        }

        $attributesForProductsChanges = array_unique($attributesForProductsChanges);
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

            if (!$listingProduct->isListed()) {
                continue;
            }

            if ($this->_ebayActions->isExistProductAction($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('title'=>true)))) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!in_array($changedListingProduct['pc_attribute'],$listingProduct->getDescriptionTemplate()->getTitleAttributes())) {
                continue;
            }

            if (!$listingProduct->getSynchronizationTemplate()->isReviseWhenChangeTitle()) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('title'=>true)));
        }
        //------------------------------------

        // Get changed listings products variations options
        //------------------------------------
        $changedListingsProductsVariationsOptions = Mage::getModel('M2ePro/ProductsChanges')->getChangedListingsProductsVariationsOptionsByAttributes(array('name'));
        //------------------------------------

        // Filter only needed listings products variations options
        //------------------------------------
        foreach ($changedListingsProductsVariationsOptions as $changedListingProductVariationOption) {

            /** @var $listingProductVariationOption Ess_M2ePro_Model_ListingsProductsVariationsOptions */
            
            $listingProductVariationOption = Mage::getModel('M2ePro/ListingsProductsVariationsOptions')->loadInstance($changedListingProductVariationOption['id']);

            if (!$listingProductVariationOption->getListingProduct()->isListed()) {
                continue;
            }

            if ($this->_ebayActions->isExistProductAction($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('variations'=>true)))) {
                continue;
            }
            
            if (!$listingProductVariationOption->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!$listingProductVariationOption->getSynchronizationTemplate()->isReviseWhenChangeTitle()) {
                continue;
            }

            if (!$listingProductVariationOption->getListingProduct()->isRevisable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('variations'=>true)));
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeSubTitleChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update subtitle');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();

        foreach ($this->_synchronizations as &$synchronization) {

            if (!$synchronization['instance']->isReviseWhenChangeSubTitle()) {
                continue;
            }

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listings */
                
                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $attributesForProductsChanges = array_merge($attributesForProductsChanges,$listing->getDescriptionTemplate()->getSubTitleAttributes());
            }
        }

        $attributesForProductsChanges = array_unique($attributesForProductsChanges);
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

            if (!$listingProduct->isListed()) {
                continue;
            }

            if ($this->_ebayActions->isExistProductAction($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('subtitle'=>true)))) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!in_array($changedListingProduct['pc_attribute'],$listingProduct->getDescriptionTemplate()->getSubTitleAttributes())) {
                continue;
            }

            if (!$listingProduct->getSynchronizationTemplate()->isReviseWhenChangeSubTitle()) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('subtitle'=>true)));
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeDescriptionChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update description');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();

        foreach ($this->_synchronizations as &$synchronization) {

            if (!$synchronization['instance']->isReviseWhenChangeDescription()) {
                continue;
            }

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listings */
                
                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $attributesForProductsChanges = array_merge($attributesForProductsChanges,$listing->getDescriptionTemplate()->getDescriptionAttributes());
            }
        }

        $attributesForProductsChanges = array_unique($attributesForProductsChanges);
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

            if (!$listingProduct->isListed()) {
                continue;
            }

            if ($this->_ebayActions->isExistProductAction($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('description'=>true)))) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!in_array($changedListingProduct['pc_attribute'],$listingProduct->getDescriptionTemplate()->getDescriptionAttributes())) {
                continue;
            }
            
            if (!$listingProduct->getSynchronizationTemplate()->isReviseWhenChangeDescription()) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('only_data'=>array('description'=>true)));
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeSellingFormatTemplatesChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update Selling Format Template');

        // Get changed templates
        //------------------------------------
        $templatesCollection = Mage::getModel('M2ePro/SellingFormatTemplates')->getCollection();
        $templatesCollection->getSelect()->where('`main_table`.`update_date` != `main_table`.`synch_date`');
        $templatesCollection->getSelect()->orWhere('`main_table`.`synch_date` IS NULL');
        $templatesArray = $templatesCollection->toArray();
        //------------------------------------
        
        // Set ebay actions for listed products
        //------------------------------------
        foreach ($templatesArray['items'] as $templateArray) {

            /** @var $template Ess_M2ePro_Model_SellingFormatTemplates */
            
            $template = Mage::getModel('M2ePro/SellingFormatTemplates')->loadInstance($templateArray['id']);

            $listings = $template->getListings(true);

            foreach ($listings as $listing) {

                /** @var $listing Ess_M2ePro_Model_Listings */
                
                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $listing->setSellingFormatTemplate($template);

                if (!$listing->getSynchronizationTemplate()->isReviseSellingFormatTemplate()) {
                    continue;
                }

                $listingsProducts = $listing->getListingsProducts(true,array('status'=>array('in'=>array(Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED))));

                foreach ($listingsProducts as $listingProduct) {

                    /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */

                    if (!$listingProduct->isListed()) {
                        continue;
                    }

                    if ($this->_ebayActions->isExistProductAction($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('all_data'=>true))) {
                        continue;
                    }

                    $listingProduct->setListing($listing);

                    if (!$listingProduct->isRevisable()) {
                        continue;
                    }

                    $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('all_data'=>true));
                }
            }

            $template->addData(array('synch_date'=>$template->getData('update_date')))->save();
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeDescriptionsTemplatesChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update description template');

        // Get changed templates
        //------------------------------------
        $templatesCollection = Mage::getModel('M2ePro/DescriptionsTemplates')->getCollection();
        $templatesCollection->getSelect()->where('`main_table`.`update_date` != `main_table`.`synch_date`');
        $templatesCollection->getSelect()->orWhere('`main_table`.`synch_date` IS NULL');
        $templatesArray = $templatesCollection->toArray();
        //------------------------------------

        // Set ebay actions for listed products
        //------------------------------------
        foreach ($templatesArray['items'] as $templateArray) {

            /** @var $template Ess_M2ePro_Model_DescriptionsTemplates */
            $template = Mage::getModel('M2ePro/DescriptionsTemplates')->loadInstance($templateArray['id']);

            $listings = $template->getListings(true);

            foreach ($listings as $listing) {

                /** @var $listing Ess_M2ePro_Model_Listings */

                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $listing->setDescriptionTemplate($template);

                if (!$listing->getSynchronizationTemplate()->isReviseDescriptionTemplate()) {
                    continue;
                }

                $listingsProducts = $listing->getListingsProducts(true,array('status'=>array('in'=>array(Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED))));

                foreach ($listingsProducts as $listingProduct) {

                    /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */

                    if (!$listingProduct->isListed()) {
                        continue;
                    }

                    if ($this->_ebayActions->isExistProductAction($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('all_data'=>true))) {
                        continue;
                    }

                    $listingProduct->setListing($listing);

                    if (!$listingProduct->isRevisable()) {
                        continue;
                    }

                    $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('all_data'=>true));
                }
            }

            $template->addData(array('synch_date'=>$template->getData('update_date')))->save();
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeListingsTemplatesChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update general template');

        // Get changed templates
        //------------------------------------
        $templatesCollection = Mage::getModel('M2ePro/ListingsTemplates')->getCollection();
        $templatesCollection->getSelect()->where('`main_table`.`update_date` != `main_table`.`synch_date`');
        $templatesCollection->getSelect()->orWhere('`main_table`.`synch_date` IS NULL');
        $templatesArray = $templatesCollection->toArray();
        //------------------------------------

        // Set ebay actions for listed products
        //------------------------------------
        foreach ($templatesArray['items'] as $templateArray) {

            /** @var $template Ess_M2ePro_Model_ListingsTemplates */
            $template = Mage::getModel('M2ePro/ListingsTemplates')->loadInstance($templateArray['id']);

            $listings = $template->getListings(true);

            foreach ($listings as $listing) {

                /** @var $listing Ess_M2ePro_Model_Listings */

                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $listing->setListingTemplate($template);

                if (!$listing->getSynchronizationTemplate()->isReviseListingTemplate()) {
                    continue;
                }

                $listingsProducts = $listing->getListingsProducts(true,array('status'=>array('in'=>array(Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED))));

                foreach ($listingsProducts as $listingProduct) {

                    /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
                    
                    if (!$listingProduct->isListed()) {
                        continue;
                    }

                    if ($this->_ebayActions->isExistProductAction($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('all_data'=>true))) {
                        continue;
                    }

                    $listingProduct->setListing($listing);

                    if (!$listingProduct->isRevisable()) {
                        continue;
                    }

                    $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE,array('all_data'=>true));
                }
            }

            $template->addData(array('synch_date'=>$template->getData('update_date')))->save();
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################   
}