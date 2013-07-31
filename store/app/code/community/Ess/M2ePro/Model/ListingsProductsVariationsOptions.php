<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_ListingsProductsVariationsOptions extends Mage_Core_Model_Abstract
{
    /**
     * @var Ess_M2ePro_Model_ListingsProductsVariations
     */
    private $_listingProductVariationModel = NULL;

    /**
     * @var Ess_M2ePro_Model_MagentoProduct
     */
    protected $_magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ListingsProductsVariationsOptions');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_ListingsProductsVariationsOptions
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
            throw new Exception('Listing product variation option does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingProductVariationId
     * @return Ess_M2ePro_Model_ListingsProductsVariationsOptions
     */
    public function loadByListingProductVariation($listingProductVariationId)
    {
        $this->load($listingProductVariationId, 'listing_product_variation_id');

        if (is_null($this->getId())) {
            throw new Exception('Listing product variation option does not exist. Probably it was deleted.');
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

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->_listingProductVariationModel = NULL;
        $this->_magentoProductModel = NULL;

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_ListingsProductsVariations
     */
    public function getListingProductVariation()
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        if (is_null($this->_listingProductVariationModel)) {
            $this->_listingProductVariationModel = Mage::getModel('M2ePro/ListingsProductsVariations')
                    ->loadInstance($this->getData('listing_product_variation_id'));
        }

        return $this->_listingProductVariationModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_ListingsProductsVariations $instance
     * @return void
     */
    public function setListingProductVariation(Ess_M2ePro_Model_ListingsProductsVariations $instance)
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        $this->_listingProductVariationModel = $instance;
    }

    /**
     * @return Ess_M2ePro_Model_ListingsProducts
     */
    public function getListingProduct()
    {
        return $this->getListingProductVariation()->getListingProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Listings
     */
    public function getListing()
    {
        return $this->getListingProductVariation()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_SellingFormatTemplates
     */
    public function getSellingFormatTemplate()
    {
        return $this->getListingProductVariation()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function getListingTemplate()
    {
        return $this->getListingProductVariation()->getListingTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_DescriptionsTemplates
     */
    public function getDescriptionTemplate()
    {
        return $this->getListingProductVariation()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_SynchronizationsTemplates
     */
    public function getSynchronizationTemplate()
    {
        return $this->getListingProductVariation()->getSynchronizationTemplate();
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_MagentoProduct
     */
    public function getMagentoProduct()
    {
        if ($this->_magentoProductModel) {
            return $this->_magentoProductModel;
        }

        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        return $this->_magentoProductModel = Mage::getModel('M2ePro/MagentoProduct')
                ->setStoreId($this->getListing()->getData('store_id'))
                ->setProductId($this->getData('product_id'));
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_MagentoProduct $instance
     * @return void
     */
    public function setMagentoProduct(Ess_M2ePro_Model_MagentoProduct $instance)
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        $this->_magentoProductModel = $instance;
    }

    // ########################################

    public function getListingProductVariationId()
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        return (int)$this->getData('listing_product_variation_id');
    }

    public function getProductId()
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        return (int)$this->getData('product_id');
    }

    public function getProductType()
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        return $this->getData('product_type');
    }

    //----------------

    public function getAttribute()
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        return $this->getData('attribute');
    }

    public function getOption()
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        return $this->getData('option');
    }

    // ########################################

    public function getSku()
    {
        if (!$this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            return $this->getMagentoProduct()->getSku();
        }

        $tempSku = '';

        $mainProduct = $this->getListingProduct()->getMagentoProduct()->getProduct();
        $simpleAttributes = $mainProduct->getOptions();

        foreach ($simpleAttributes as $tempAttribute) {

            if (!(bool)(int)$tempAttribute->getData('is_require')) {
                continue;
            }

            if (!in_array($tempAttribute->getType(), array('drop_down', 'radio', 'multiple', 'checkbox'))) {
                continue;
            }

            if ($tempAttribute->getData('default_title') != $this->getAttribute() &&
                $tempAttribute->getData('store_title') != $this->getAttribute() &&
                $tempAttribute->getData('title') != $this->getAttribute()) {
                continue;
            }

            foreach ($tempAttribute->getValues() as $tempOption) {

                if ($tempOption->getData('default_title') != $this->getOption() &&
                    $tempOption->getData('store_title') != $this->getOption() &&
                    $tempOption->getData('title') != $this->getOption()) {
                    continue;
                }

                if (!is_null($tempOption->getData('sku')) &&
                    $tempOption->getData('sku') !== false) {
                    $tempSku = $tempOption->getData('sku');
                }

                break 2;
            }
        }

        return $tempSku;
    }

    public function getQty()
    {
        $qty = 0;

        $src = $this->getSellingFormatTemplate()->getQtySource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_SINGLE:
                $qty = 1;
                break;

            case Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_NUMBER:
                $qty = (int)$src['value'];
                break;

            case Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_ATTRIBUTE:
                $qty = (int)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            default:
            case Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_PRODUCT:
                $qty = (int)$this->getMagentoProduct()->getQty();
                break;
        }

        if (!$this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            if (!$this->getMagentoProduct()->getStockAvailability() ||
                $this->getMagentoProduct()->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED)  {
                // Out of stock or disabled Item? Set QTY = 0
                $qty = 0;
            }
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    // ########################################

    public function getPrice()
    {
        $price = 0;

        // Configurable product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType()) {

            if ($this->getSellingFormatTemplate()->isPriceVariationModeParent()) {
                $price = $this->getConfigurablePriceParent();
            } else {
                $price = $this->getBaseProductPrice();
            }

        // Bundle product
        } else if ($this->getListingProduct()->getMagentoProduct()->isBundleType()) {

            if ($this->getSellingFormatTemplate()->isPriceVariationModeParent()) {
                $price = $this->getBundlePriceParent();
            } else {
                $price = $this->getBaseProductPrice();
            }

        // Simple with custom options
        } else if ($this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            $price = $this->getSimpleWithCustomOptionsPrice();
        // Grouped product
        } else if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {
            $price = $this->getBaseProductPrice();
        }

        $price < 0 && $price = 0;

        return $price;
    }

    //----------------
    
    protected function getConfigurablePriceParent()
    {
        $price = 0;

        $mainProduct = $this->getListingProduct()->getMagentoProduct()->getProduct();
        $configurableAttributes = $mainProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($mainProduct);

        foreach ($configurableAttributes as $tempAttribute) {

            if ((!isset($tempAttribute['label']) || strtolower($tempAttribute['label']) != strtolower($this->getAttribute())) &&
                (!isset($tempAttribute['frontend_label']) || strtolower($tempAttribute['frontend_label']) != strtolower($this->getAttribute())) &&
                (!isset($tempAttribute['store_label']) || strtolower($tempAttribute['store_label']) != strtolower($this->getAttribute()))) {
                continue;
            }

            foreach ($tempAttribute['values'] as $tempOption) {

                if ((!isset($tempOption['label']) || strtolower($tempOption['label']) != strtolower($this->getOption())) &&
                    (!isset($tempOption['default_label']) || strtolower($tempOption['default_label']) != strtolower($this->getOption())) &&
                    (!isset($tempOption['store_label']) || strtolower($tempOption['store_label']) != strtolower($this->getOption()))) {
                    continue;
                }

                if ((bool)(int)$tempOption['is_percent']) {
                    // Base Price of Main product.
                    $src = $this->getSellingFormatTemplate()->getBuyItNowPriceSource();
                    $basePrice = $this->getListingProduct()->getBaseProductPrice($src['mode'],$src['attribute']);
                    $price = ($basePrice * (float)$tempOption['pricing_value']) / 100;
                } else {
                    $price = (float)$tempOption['pricing_value'];
                }

                break 2;
            }
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getBundlePriceParent()
    {
        $price = 0;

        $mainProduct = $this->getListingProduct()->getMagentoProduct()->getProduct();
        $mainProductInstance = $mainProduct->getTypeInstance(true);
        $bundleAttributes = $mainProductInstance->getOptionsCollection($mainProduct);

        foreach ($bundleAttributes as $tempAttribute) {

            if (!(bool)(int)$tempAttribute->getData('required')) {
                continue;
            }

            if (is_null($tempAttribute->getData('default_title')) ||
                strtolower($tempAttribute->getData('default_title')) != strtolower($this->getAttribute())) {
                continue;
            }

            $tempOptions = $mainProductInstance->getSelectionsCollection(array(0 => $tempAttribute->getId()), $mainProduct)->getItems();

            foreach ($tempOptions as $tempOption) {

                if ((int)$tempOption->getId() != $this->getProductId()) {
                    continue;
                }

                if ((bool)(int)$tempOption->getData('selection_price_type')) {
                    // Base Price of Main product.
                    $src = $this->getSellingFormatTemplate()->getBuyItNowPriceSource();
                    $basePrice = $this->getListingProduct()->getBaseProductPrice($src['mode'],$src['attribute']);
                    $price = ($basePrice * (float)$tempOption->getData('selection_price_value')) / 100;
                } else {
                    $price = (float)$tempOption->getData('selection_price_value');
                }

                break 2;
            }
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getSimpleWithCustomOptionsPrice()
    {
        $price = 0;

        $mainProduct = $this->getListingProduct()->getMagentoProduct()->getProduct();
        $simpleAttributes = $mainProduct->getOptions();

        foreach ($simpleAttributes as $tempAttribute) {

            if (!(bool)(int)$tempAttribute->getData('is_require')) {
                continue;
            }

            if (!in_array($tempAttribute->getType(), array('drop_down', 'radio', 'multiple', 'checkbox'))) {
                continue;
            }

            if ((is_null($tempAttribute->getData('default_title')) || strtolower($tempAttribute->getData('default_title')) != strtolower($this->getAttribute())) &&
                (is_null($tempAttribute->getData('store_title')) || strtolower($tempAttribute->getData('store_title')) != strtolower($this->getAttribute())) &&
                (is_null($tempAttribute->getData('title')) || strtolower($tempAttribute->getData('title')) != strtolower($this->getAttribute()))) {
                continue;
            }

            foreach ($tempAttribute->getValues() as $tempOption) {

                if ((is_null($tempOption->getData('default_title')) || strtolower($tempOption->getData('default_title')) != strtolower($this->getOption())) &&
                    (is_null($tempOption->getData('store_title')) || strtolower($tempOption->getData('store_title')) != strtolower($this->getOption())) &&
                    (is_null($tempOption->getData('title')) || strtolower($tempOption->getData('title')) != strtolower($this->getOption()))) {
                    continue;
                }

                if (!is_null($tempOption->getData('price_type')) &&
                    $tempOption->getData('price_type') !== false) {

                    switch ($tempOption->getData('price_type')) {
                        case 'percent':
                            $src = $this->getSellingFormatTemplate()->getBuyItNowPriceSource();
                            $basePrice = $this->getListingProduct()->getBaseProductPrice($src['mode'],$src['attribute']);
                            $price = ($basePrice * (float)$tempOption->getData('price')) / 100;
                            break;
                        case 'fixed':
                            $price = (float)$tempOption->getData('price');
                            break;
                    }
                }

                break 2;
            }
        }

        $price < 0 && $price = 0;

        return $price;
    }

    //----------------

    protected function getBaseProductPrice()
    {
        $price = 0;

        $src = $this->getSellingFormatTemplate()->getBuyItNowPriceSource();

        switch ($src['mode']) {

            case Ess_M2ePro_Model_SellingFormatTemplates::PRICE_NONE:
                $price = 0;
                break;

            case Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL:
                $price = $this->getMagentoProduct()->getSpecialPrice();
                $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                break;

            case Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            default:
            case Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT:
                $price = $this->getMagentoProduct()->getPrice();
                break;
        }

        $price < 0 && $price = 0;

        return $price;
    }

    // ########################################

    public function getMainImageLink()
    {
        $imageLink = '';

        if ($this->getDescriptionTemplate()->isImageMainModeProduct()) {
            $imageLink = $this->getMagentoProduct()->getImageLink('image');
        }
        if ($this->getDescriptionTemplate()->isImageMainModeAttribute()) {
            $src = $this->getDescriptionTemplate()->getImageMainSource();
            $imageLink = $this->getMagentoProduct()->getImageLink($src['attribute']);
        }

        return $imageLink;
    }
    
    public function getImagesForEbay()
    {
        if ($this->getDescriptionTemplate()->isImageMainModeNone()) {
            return array();
        }

        $mainImage = $this->getMainImageLink();

        if ($mainImage == '') {
            return array();
        }

        return array($mainImage);
    }

    // ########################################
}