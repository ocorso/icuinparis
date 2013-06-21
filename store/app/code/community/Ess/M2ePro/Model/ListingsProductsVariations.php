<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_ListingsProductsVariations extends Mage_Core_Model_Abstract
{
    const DELETE_NO   = 0;
    const DELETE_YES  = 1;

    const ADD_NO   = 0;
    const ADD_YES  = 1;

    /**
     * @var Ess_M2ePro_Model_ListingsProducts
     */
    private $_listingProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ListingsProductsVariations');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_ListingsProductsVariations
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Listing product variation does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingProductId
     * @return Ess_M2ePro_Model_ListingsProductsVariations
     */
    public function loadByListingProduct($listingProductId)
    {
        $this->load($listingProductId,'listing_product_id');

        if (is_null($this->getId())) {
             throw new Exception('Listing product variation does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingProductVariationOptionId
     * @return Ess_M2ePro_Model_ListingsProductsVariations
     */
    public function loadByListingProductVariationOption($listingProductVariationOptionId)
    {
         $tempModel = Mage::getModel('M2ePro/ListingsProductsVariationsOptions')->load($listingProductVariationOptionId);

         if (is_null($tempModel->getId())) {
             throw new Exception('Listing product variation option does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('listing_product_variation_id'));
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

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $listingsProductsVariationsOptions = $this->getListingsProductsVariationsOptions(true);
        foreach ($listingsProductsVariationsOptions as $listingProductVariationOption) {
            /** @var $listingProductVariationOption Ess_M2ePro_Model_ListingsProductsVariationsOptions */
            $listingProductVariationOption->deleteInstance();
        }

        $this->_listingProductModel = NULL;

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listings
     */
    public function getListing()
    {
        return $this->getListingProduct()->getListing();
    }
    
    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_ListingsProducts
     */
    public function getListingProduct()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        if (is_null($this->_listingProductModel)) {
            $this->_listingProductModel = Mage::getModel('M2ePro/ListingsProducts')
                                            ->loadInstance($this->getData('listing_product_id'));
        }

        return $this->_listingProductModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_ListingsProducts $instance
     * @return void
     */
    public function setListingProduct(Ess_M2ePro_Model_ListingsProducts $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $this->_listingProductModel = $instance;
    }

    /**
     * @return Ess_M2ePro_Model_SellingFormatTemplates
     */
    public function getSellingFormatTemplate()
    {
        return $this->getListingProduct()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function getListingTemplate()
    {
        return $this->getListingProduct()->getListingTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_DescriptionsTemplates
     */
    public function getDescriptionTemplate()
    {
        return $this->getListingProduct()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_SynchronizationsTemplates
     */
    public function getSynchronizationTemplate()
    {
        return $this->getListingProduct()->getSynchronizationTemplate();
    }

    // ########################################

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @return array
     */
    public function getListingsProductsVariationsOptions($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/ListingsProductsVariationsOptions')->getCollection();
        $tempCollection->addFieldToFilter('listing_product_variation_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $tempInstance = Mage::getModel('M2ePro/ListingsProductsVariationsOptions')
                                        ->loadInstance($item['id']);
                $tempInstance->setListingProductVariation($this);
                $resultArray[] = $tempInstance;
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    // ########################################

    public function getListingProductId()
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        return (int)$this->getData('listing_product_id');
    }

    //----------------
    
    public function getEbayPrice()
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        return (float)$this->getData('ebay_price');
    }

    public function getEbayQty()
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        return (int)$this->getData('ebay_qty');
    }

    public function getEbayQtySold()
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        return (int)$this->getData('ebay_qty_sold');
    }

    //----------------

    public function isAdd()
    {
        return (int)$this->getData('add') == self::ADD_YES;
    }

    public function isDelete()
    {
        return (int)$this->getData('delete') == self::DELETE_YES;
    }

    // ########################################

    public function getSku()
    {
        if ($this->isDelete()) {
            return '';
        }

        if (!$this->getListingTemplate()->isSkuEnabled()) {
            return '';
        }

        $sku = '';
        
        // Options Models
        $options = $this->getListingsProductsVariationsOptions(true);

        // Configurable, Grouped product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
            $this->getListingProduct()->getMagentoProduct()->isGroupedType()) {

            foreach ($options as $option) {
                $sku = $option->getSku();
                break;
            }

        // Bundle product
        } else if ($this->getListingProduct()->getMagentoProduct()->isBundleType()) {

            foreach ($options as $option) {
                $sku != '' && $sku .= '-';
                $sku .= $option->getSku();
            }
            
        // Simple with options product
        } else if ($this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

            foreach ($options as $option) {
                $sku != '' && $sku .= '-';
                $tempSku = $option->getSku();
                if ($tempSku == '') {
                    $sku .= Mage::helper('M2ePro')->convertStringToSku($option->getOption());
                } else {
                    $sku .= $tempSku;
                }
            }
        }

        return $sku;
    }

    public function getQty()
    {
        $qty = 0;

        if ($this->isDelete()) {
            return $qty;
        }

        // Options Models
        $options = $this->getListingsProductsVariationsOptions(true);

        // Configurable, Grouped, Simple with options product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
            $this->getListingProduct()->getMagentoProduct()->isGroupedType() ||
            $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

            foreach ($options as $option) {
                $qty = $option->getQty();
                break;
            }

        // Bundle product
        } else {

            $optionsQtyList = array();
            foreach ($options as $option) {
               /** @var $option Ess_M2ePro_Model_ListingsProductsVariationsOptions */
               $optionsQtyList[] = $option->getQty();
            }

            $qty = min($optionsQtyList);
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    public function getPrice()
    {
        $price = 0;

        if ($this->isDelete()) {
            return $price;
        }

        // Options Models
        $options = $this->getListingsProductsVariationsOptions(true);

        // Buy it now src
        $buyItNowSrc = $this->getSellingFormatTemplate()->getBuyItNowPriceSource();

        // Configurable, Bundle, Simple with options product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
            $this->getListingProduct()->getMagentoProduct()->isBundleType() ||
            $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

            if ($this->getSellingFormatTemplate()->isPriceVariationModeParent() ||
                $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

                // Base Price of Main product.
                $price = $this->getListingProduct()->getBaseProductPrice($buyItNowSrc['mode'],$buyItNowSrc['attribute']);

                foreach ($options as $option) {
                    /** @var $option Ess_M2ePro_Model_ListingsProductsVariationsOptions */
                    $price += $option->getPrice();
                }

            } else {

                $isBundle = $this->getListingProduct()->getMagentoProduct()->isBundleType();

                foreach ($options as $option) {

                    /** @var $option Ess_M2ePro_Model_ListingsProductsVariationsOptions */

                    if ($isBundle) {
                        $price += $option->getPrice();
                        continue;
                    }

                    $price = $option->getPrice();
                    break;
                }
            }

        // Grouped product
        } else if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_ListingsProductsVariationsOptions */
                $price = $option->getPrice();
                break;
            }
        }

        $price < 0 && $price = 0;

        return $this->getSellingFormatTemplate()->parsePrice($price, $buyItNowSrc['coefficient']);
    }

    // ########################################

    public function getStatus()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('status');
    }

    //----------------
    
    public function isNotListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED;
    }

    public function isListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED;
    }

    public function isSold()
    {
        return $this->getStatus() == Ess_M2ePro_Model_ListingsProducts::STATUS_SOLD;
    }

    public function isStopped()
    {
        return $this->getStatus() == Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED;
    }

    public function isFinished()
    {
        return $this->getStatus() == Ess_M2ePro_Model_ListingsProducts::STATUS_FINISHED;
    }

    //----------------

    public function isListable()
    {
        return $this->isNotListed() || $this->isSold() || $this->isStopped() || $this->isFinished();
    }

    public function isRelistable()
    {
        return $this->isSold() || $this->isStopped() || $this->isFinished();
    }

    public function isRevisable()
    {
        return $this->isListed();
    }

    public function isStoppable()
    {
        return $this->isListed();
    }

     // ########################################
}