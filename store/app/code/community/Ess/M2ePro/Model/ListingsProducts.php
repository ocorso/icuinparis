<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_ListingsProducts extends Mage_Core_Model_Abstract
{
    const STATUS_NOT_LISTED = 0;
    const STATUS_SOLD = 1;
    const STATUS_LISTED = 2;
    const STATUS_STOPPED = 3;
    const STATUS_FINISHED = 4;

    const STATUS_CHANGER_UNKNOWN = 0;
    const STATUS_CHANGER_SYNCH = 1;
    const STATUS_CHANGER_USER = 2;
    const STATUS_CHANGER_EBAY = 3;
    const STATUS_CHANGER_OBSERVER = 4;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Listings
     */
    protected $_listingModel = NULL;

    /**
     * @var Ess_M2ePro_Model_MagentoProduct
     */
    protected $_magentoProductModel = NULL;

    /**
     * @var Ess_M2ePro_Model_EbayItems
     */
    protected $_eBayItemModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ListingsProducts');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_ListingsProducts
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
            throw new Exception('Listing product does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingId
     * @return Ess_M2ePro_Model_ListingsProducts
     */
    public function loadByListing($listingId)
    {
        $this->load($listingId, 'listing_id');

        if (is_null($this->getId())) {
            throw new Exception('Listing product does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $productId
     * @return Ess_M2ePro_Model_ListingsProducts
     */
    public function loadByProduct($productId)
    {
        $this->load($productId, 'product_id');

        if (is_null($this->getId())) {
            throw new Exception('Listing product does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $ebayItemId
     * @return Ess_M2ePro_Model_ListingsProducts
     */
    public function loadByEbayItem($ebayItemId)
    {
        $collectionArray = Mage::getModel('M2ePro/ListingsProducts')
                ->getCollection()
                ->addFieldToFilter('ebay_items_id', $ebayItemId)
                ->toArray();

        if ($collectionArray['totalRecords'] == 0) {
            throw new Exception('eBay item does not exist. Probably it was deleted.');
        }

        return $this->loadInstance($collectionArray['items'][0]['id']);
    }

    /**
     * @throws LogicException
     * @param  int $listingProductVariationId
     * @return Ess_M2ePro_Model_ListingsProducts
     */
    public function loadByListingProductVariation($listingProductVariationId)
    {
        $tempModel = Mage::getModel('M2ePro/ListingsProductsVariations')->load($listingProductVariationId);

        if (is_null($tempModel->getId())) {
            throw new Exception('Listing product variation does not exist. Probably it was deleted.');
        }

        return $this->loadInstance($tempModel->getData('listing_product_id'));
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

        if ($this->getStatus() == self::STATUS_LISTED) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $listingsProductsVariations = $this->getListingsProductsVariations(true);
        foreach ($listingsProductsVariations as $listingProductVariation) {
            $listingProductVariation->deleteInstance();
        }

        Mage::getModel('M2ePro/ListingsLogs')
                ->addProductMessage($this->getData('listing_id'),
                                    $this->getData('product_id'),
                                    Ess_M2ePro_Model_ListingsLogs::INITIATOR_UNKNOWN,
                                    NULL,
                                    Ess_M2ePro_Model_ListingsLogs::ACTION_DELETE_PRODUCT_FROM_LISTING,
                                    // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully deleted');
                                    'Item was successfully deleted',
                                    Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE,
                                    Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);

        $this->_listingModel = NULL;
        $this->_magentoProductModel = NULL;
        $this->_eBayItemModel = NULL;

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Listings
     */
    public function getListing()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        if (is_null($this->_listingModel)) {
            $this->_listingModel = Mage::getModel('M2ePro/Listings')->loadInstance($this->getData('listing_id'));
        }

        return $this->_listingModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_Listings $instance
     * @return void
     */
    public function setListing(Ess_M2ePro_Model_Listings $instance)
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        $this->_listingModel = $instance;
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
            throw new Exception('Method require loaded instance first');
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
            throw new Exception('Method require loaded instance first');
        }

        $this->_magentoProductModel = $instance;
    }

    /**
     * @return Ess_M2ePro_Model_EbayItems
     */
    public function getEbayItem()
    {
        if ($this->_eBayItemModel) {
            return $this->_eBayItemModel;
        }

        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        if (!$this->getData('ebay_items_id')) {
            throw new LogicException('Product is not listed on eBay');
        }

        return $this->_eBayItemModel = Mage::getModel('M2ePro/EbayItems')
                ->loadInstance($this->getData('ebay_items_id'));
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_EbayItems $instance
     * @return void
     */
    public function setEbayItem(Ess_M2ePro_Model_EbayItems $instance)
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        $this->_eBayItemModel = $instance;
    }

    /**
     * @return Ess_M2ePro_Model_SellingFormatTemplates
     */
    public function getSellingFormatTemplate()
    {
        return $this->getListing()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function getListingTemplate()
    {
        return $this->getListing()->getListingTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_DescriptionsTemplates
     */
    public function getDescriptionTemplate()
    {
        return $this->getListing()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_SynchronizationsTemplates
     */
    public function getSynchronizationTemplate()
    {
        return $this->getListing()->getSynchronizationTemplate();
    }

    // ########################################

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @return array
     */
    public function getListingsProductsVariations($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/ListingsProductsVariations')->getCollection();
        $tempCollection->addFieldToFilter('listing_product_id', $this->getId());
        foreach ($filters as $field => $filter) {
            $tempCollection->addFieldToFilter('`' . $field . '`', $filter);
        }

        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $tempInstance = Mage::getModel('M2ePro/ListingsProductsVariations')
                        ->loadInstance($item['id']);
                $tempInstance->setListingProduct($this);
                $resultArray[] = $tempInstance;
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    // ########################################

    public static function getInstanceByEbayItem($ebayItem)
    {
        // Prepare tables names
        //-----------------------------
        $ebayItemsTable = Mage::getResourceModel('M2ePro/EbayItems')->getMainTable();
        //-----------------------------

        // Get listing product
        //-----------------------------
        $collection = Mage::getModel('M2ePro/ListingsProducts')->getCollection();
        $collection->getSelect()->join(array('mei' => $ebayItemsTable), '(main_table.ebay_items_id = mei.id AND mei.item_id = ' . $ebayItem . ')', array());
        $listingsProducts = $collection->toArray();
        //-----------------------------

        // Return instance or false
        //-----------------------------
        if ($listingsProducts['totalRecords'] == 0) {
            return false;
        } else {
            return Mage::getModel('M2ePro/ListingsProducts')->loadInstance($listingsProducts['items'][0]['id']);
        }
        //-----------------------------
    }

    public function getEbayItemIdReal()
    {
        return $this->getEbayItem()->getItemId();
    }

    // ########################################

    public function getListingId()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('listing_id');
    }

    public function getProductId()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('product_id');
    }

    public function getEbayItemsId()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('ebay_items_id');
    }

    //----------------

    public function getEbayStartPrice()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return (float)$this->getData('ebay_start_price');
    }

    public function getEbayReservePrice()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return (float)$this->getData('ebay_reserve_price');
    }

    public function getEbayBuyItNowPrice()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return (float)$this->getData('ebay_buyitnow_price');
    }

    //----------------

    public function getEbayQty()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('ebay_qty');
    }

    public function getEbayQtySold()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('ebay_qty_sold');
    }

    public function getEbayBids()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('ebay_bids');
    }

    //----------------

    public function getEbayStartDate()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return $this->getData('ebay_start_date');
    }

    public function getEbayEndDate()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return $this->getData('ebay_end_date');
    }

    //----------------

    public function getStatus()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('status');
    }

    public function getStatusChanger()
    {
        if (is_null($this->getId())) {
            throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('status_changer');
    }

    // ########################################

    public function getSku()
    {
        if ($this->getListingTemplate()->isSkuEnabled()) {
            return $this->getMagentoProduct()->getSku();
        }
        return '';
    }

    public function getDuration()
    {
        $src = $this->getSellingFormatTemplate()->getDurationSource();

        if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::DURATION_TYPE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getItemCondition()
    {
        $src = $this->getListingTemplate()->getItemConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_ListingsTemplates::CATEGORIES_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getProductDetail($type)
    {
        $src = $this->getListingTemplate()->getProductDetailSource($type);

        if (is_null($src) || $src['mode'] == Ess_M2ePro_Model_ListingsTemplates::OPTION_NONE) {
            return NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_ListingsTemplates::OPTION_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    //-------------------------------

    public function getTitle()
    {
        $title = '';
        $src = $this->getDescriptionTemplate()->getTitleSource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_DescriptionsTemplates::TITLE_MODE_PRODUCT:
                $title = $this->getMagentoProduct()->getName();
                break;

            case Ess_M2ePro_Model_DescriptionsTemplates::TITLE_MODE_CUSTOM:
                $title = Mage::helper('M2ePro/TemplatesParser')->parseTemplate($src['template'], $this->getMagentoProduct()->getProduct());
                break;

            default:
                $title = $this->getMagentoProduct()->getName();
                break;
        }

        if ($this->getDescriptionTemplate()->isCutLongTitles()) {
            $title = $this->getDescriptionTemplate()->cutLongTitles($title);
        }

        return $title;
    }

    public function getSubTitle()
    {
        $subTitle = '';
        $src = $this->getDescriptionTemplate()->getSubTitleSource();

        if ($src['mode'] == Ess_M2ePro_Model_DescriptionsTemplates::SUBTITLE_MODE_CUSTOM) {
            $subTitle = Mage::helper('M2ePro/TemplatesParser')->parseTemplate($src['template'], $this->getMagentoProduct()->getProduct());
            if ($this->getDescriptionTemplate()->isCutLongTitles()) {
                $subTitle = $this->getDescriptionTemplate()->cutLongTitles($subTitle, 55);
            }
        }

        return $subTitle;
    }

    public function getDescription()
    {
        $description = '';
        $src = $this->getDescriptionTemplate()->getDescriptionSource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_DescriptionsTemplates::DESCRIPTION_MODE_PRODUCT:
                $description = $this->getMagentoProduct()->getProduct()->getDescription();
                break;

            case Ess_M2ePro_Model_DescriptionsTemplates::DESCRIPTION_MODE_SHORT:
                $description = $this->getMagentoProduct()->getProduct()->getShortDescription();
                break;

            case Ess_M2ePro_Model_DescriptionsTemplates::DESCRIPTION_MODE_CUSTOM:
                $description = Mage::helper('M2ePro/TemplatesParser')->parseTemplate($src['template'], $this->getMagentoProduct()->getProduct());
                break;

            default:
                $description = $this->getMagentoProduct()->getProduct()->getDescription();
                break;
        }

        return str_replace(array('<![CDATA[', ']]>'), '', $description);
    }

    //-------------------------------

    public function getListingType()
    {
        $src = $this->getSellingFormatTemplate()->getListingTypeSource();

        if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::LISTING_TYPE_ATTRIBUTE) {
            $ebayStringType = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            switch ($ebayStringType) {
                case Ess_M2ePro_Model_SellingFormatTemplates::EBAY_LISTING_TYPE_FIXED:
                    return Ess_M2ePro_Model_SellingFormatTemplates::LISTING_TYPE_FIXED;
                case Ess_M2ePro_Model_SellingFormatTemplates::EBAY_LISTING_TYPE_AUCTION:
                    return Ess_M2ePro_Model_SellingFormatTemplates::LISTING_TYPE_AUCTION;
            }
            throw new LogicException('Invalid listing type in attribute.');
        }

        return $src['mode'];
    }

    public function isListingTypeFixed()
    {
        return $this->getListingType() == Ess_M2ePro_Model_SellingFormatTemplates::LISTING_TYPE_FIXED;
    }

    public function isListingTypeAuction()
    {
        return $this->getListingType() == Ess_M2ePro_Model_SellingFormatTemplates::LISTING_TYPE_AUCTION;
    }

    // ########################################

    public function getStartPrice()
    {
        $price = 0;

        if (!$this->isListingTypeAuction()) {
            return $price;
        }

        $src = $this->getSellingFormatTemplate()->getStartPriceSource();
        $price = $this->getBaseProductPrice($src['mode'],$src['attribute']);

        return $this->getSellingFormatTemplate()->parsePrice($price, $src['coefficient']);
    }

    public function getReservePrice()
    {
        $price = 0;

        if (!$this->isListingTypeAuction()) {
            return $price;
        }

        $src = $this->getSellingFormatTemplate()->getReservePriceSource();
        $price = $this->getBaseProductPrice($src['mode'],$src['attribute']);

        return $this->getSellingFormatTemplate()->parsePrice($price, $src['coefficient']);
    }

    public function getBuyItNowPrice()
    {
        if ($this->isListingTypeFixed() &&
            $this->getListingTemplate()->isVariationMode() &&
            !$this->getMagentoProduct()->isSimpleTypeWithoutCustomOptions()) {

            $variations = $this->getListingsProductsVariations(true, array('delete' => Ess_M2ePro_Model_ListingsProductsVariations::DELETE_NO));

            if (count($variations) > 0) {

                $pricesList = array();
                foreach ($variations as $variation) {
                    /** @var $variation Ess_M2ePro_Model_ListingsProductsVariations */
                    $pricesList[] = $variation->getPrice();
                }
                return count($pricesList) > 0 ? min($pricesList) : 0;
            }
        }

        $src = $this->getSellingFormatTemplate()->getBuyItNowPriceSource();
        $price = $this->getBaseProductPrice($src['mode'],$src['attribute']);

        return $this->getSellingFormatTemplate()->parsePrice($price, $src['coefficient']);
    }

    //-------------------------------

    public function getBaseProductPrice($mode, $attribute = '')
    {
        $price = 0;

        switch ($mode) {
            
            case Ess_M2ePro_Model_SellingFormatTemplates::PRICE_NONE:
                $price = 0;
                break;

            case Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $price = $this->getBaseGroupedProductPrice(Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL);
                } else {
                    $price = $this->getMagentoProduct()->getSpecialPrice();
                    $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                }
                break;

            case Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($attribute);
                break;

            default:
            case Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $price = $this->getBaseGroupedProductPrice(Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT);
                } else {
                    $price = $this->getMagentoProduct()->getPrice();
                }
                break;
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getBaseGroupedProductPrice($priceType)
    {
        $price = 0;
        
        $product = $this->getMagentoProduct()->getProduct();

        foreach ($product->getTypeInstance()->getAssociatedProducts() as $tempProduct) {

            $tempPrice = 0;
            $tempProduct = Mage::getModel('M2ePro/MagentoProduct')->setProduct($tempProduct);
            
            switch ($priceType) {
                case Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT:
                    $tempPrice = $tempProduct->getPrice();
                    break;
                case Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL:
                    $tempPrice = $tempProduct->getSpecialPrice();
                    $tempPrice <= 0 && $tempPrice = $tempProduct->getPrice();
                    break;
            }

            $tempPrice = (float)$tempPrice;

            if ($tempPrice < $price || $price == 0) {
                $price = $tempPrice;
            }
        }

        $price < 0 && $price = 0;

        return $price;
    }

    // ########################################

    public function getQty($productMode = false)
    {
        if ($this->isListingTypeAuction()) {
            if ($productMode) {
                return $this->_getProductGeneralQty();
            }
            return 1;
        }

        // variation product or simple product with custom options and variation enabled
        if ($this->getListingTemplate()->isVariationMode() &&
            !$this->getMagentoProduct()->isSimpleTypeWithoutCustomOptions()) {

            $variations = $this->getListingsProductsVariations(true, array('delete' => Ess_M2ePro_Model_ListingsProductsVariations::DELETE_NO));

            if (count($variations) > 0) {

                $totalQty = 0;
                foreach ($variations as $variation) {
                    /** @var $variation Ess_M2ePro_Model_ListingsProductsVariations */
                    $totalQty += $variation->getQty();
                }
                return (int)floor($totalQty);
            }
        }

        $qty = 0;
        $src = $this->getSellingFormatTemplate()->getQtySource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_SINGLE:
                if ($productMode) {
                    $qty = $this->_getProductGeneralQty();
                } else {
                    $qty = 1;
                }
                break;

            case Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_NUMBER:
                if ($productMode) {
                    $qty = $this->_getProductGeneralQty();
                } else {
                    $qty = $src['value'];
                }
                break;

            case Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_ATTRIBUTE:
                $qty = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            default:
            case Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_PRODUCT:
                $qty = $this->_getProductGeneralQty();
                break;
        }

        return (int)floor($qty);
    }

    //-------------------------------

    protected function _getProductGeneralQty()
    {
        if ($this->getMagentoProduct()->isVariationOnlyType() &&
            !$this->getListingTemplate()->isVariationMode()) {
            return $this->_getOnlyVariationProductQty();
        }
        return (int)floor($this->getMagentoProduct()->getQty());
    }

    protected function _getOnlyVariationProductQty()
    {
        if ($this->getMagentoProduct()->isBundleType()) {
            return $this->_getBundleProductQty();
        }
        if ($this->getMagentoProduct()->isGroupedType()) {
            return $this->_getGroupedProductQty();
        }
        if ($this->getMagentoProduct()->isConfigurableType()) {
            return $this->_getConfigurableProductQty();
        }

        return 0;
    }

    //-------------------------------

    protected function _getConfigurableProductQty()
    {
        $product = $this->getMagentoProduct()->getProduct();
        $totalQty = 0;
        foreach ($product->getTypeInstance()->getUsedProducts() as $childProduct) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct);
            $qty = $stockItem->getQty();
            if ($stockItem->getIsInStock() == 1) {
                $totalQty += $qty;
            }
        }
        return (int)floor($totalQty);
    }

    protected function _getGroupedProductQty()
    {
        $product = $this->getMagentoProduct()->getProduct();
        $totalQty = 0;
        foreach ($product->getTypeInstance()->getAssociatedProducts() as $childProduct) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct);
            $qty = $stockItem->getQty();
            if ($stockItem->getIsInStock() == 1) {
                $totalQty += $qty;
            }
        }
        return (int)floor($totalQty);
    }

    protected function _getBundleProductQty()
    {
        $product = $this->getMagentoProduct()->getProduct();

        // Prepare bundle options format usable for search
        $productInstance = $product->getTypeInstance(true);
        $optionCollection = $productInstance->getOptionsCollection($product);
        $optionsData = $optionCollection->getData();

        foreach ($optionsData as $singleOption) {
            // Save QTY, before calculate = 0
            $bundleOptionsArray[$singleOption['option_id']] = 0;
        }

        $selectionsCollection = $productInstance->getSelectionsCollection($optionCollection->getAllIds(), $product);
        $_items = $selectionsCollection->getItems();
        foreach ($_items as $_item) {
            $itemInfoAsArray = $_item->toArray();
            if (isset($bundleOptionsArray[$itemInfoAsArray['option_id']])) {
                // For each option item inc total QTY
                if ($itemInfoAsArray['stock_item']['is_in_stock'] != 1) {
                    // Skip get qty for options product that not in stock
                    continue;
                }
                $addQty = $itemInfoAsArray['stock_item']['qty'];
                // Only positive
                $bundleOptionsArray[$itemInfoAsArray['option_id']] += (($addQty < 0) ? 0 : $addQty);
            }
        }

        // Get min of qty product for all options
        $minQty = -1;
        foreach ($bundleOptionsArray as $singleBundle) {
            if ($singleBundle < $minQty || $minQty == -1) {
                $minQty = $singleBundle;
            }
        }

        $minQty < 0 && $minQty = 0;
        
        return (int)floor($minQty);
    }

    // ########################################

    public function getMainCategory()
    {
        $src = $this->getListingTemplate()->getCategoriesSource();

        if ($src['mode'] == Ess_M2ePro_Model_ListingsTemplates::CATEGORIES_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['main_attribute']);
        }

        return $src['main_value'];
    }

    public function getSecondaryCategory()
    {
        $src = $this->getListingTemplate()->getCategoriesSource();

        if ($src['mode'] == Ess_M2ePro_Model_ListingsTemplates::CATEGORIES_MODE_ATTRIBUTE) {
            return $src['secondary_attribute'] ? $this->getMagentoProduct()->getAttributeValue($src['secondary_attribute']) : 0;
        }

        return $src['secondary_value'];
    }

    //-------------------------------

    public function getMainStoreCategory()
    {
        $src = $this->getListingTemplate()->getStoreCategoriesSource();

        $category = 0;
        switch ($src['main_mode']) {
            case Ess_M2ePro_Model_ListingsTemplates::STORE_CATEGORY_EBAY_VALUE:
                $category = $src['main_value'];
                break;

            case Ess_M2ePro_Model_ListingsTemplates::STORE_CATEGORY_CUSTOM_ATTRIBUTE:
                $category = $this->getMagentoProduct()->getAttributeValue($src['main_attribute']);
                break;
        }

        return $category;
    }

    public function getSecondaryStoreCategory()
    {
        $src = $this->getListingTemplate()->getStoreCategoriesSource();

        $category = 0;
        switch ($src['secondary_mode']) {
            case Ess_M2ePro_Model_ListingsTemplates::STORE_CATEGORY_EBAY_VALUE:
                $category = $src['secondary_value'];
                break;

            case Ess_M2ePro_Model_ListingsTemplates::STORE_CATEGORY_CUSTOM_ATTRIBUTE:
                $category = $this->getMagentoProduct()->getAttributeValue($src['secondary_attribute']);
                break;
        }

        return $category;
    }

    //-------------------------------

    public function getBestOfferAcceptPrice()
    {
        if (!$this->isListingTypeFixed()) {
            return 0;
        }

        if (!$this->getSellingFormatTemplate()->isBestOfferEnabled()) {
            return 0;
        }

        if ($this->getSellingFormatTemplate()->isBestOfferAcceptModeNo()) {
            return 0;
        }

        $src = $this->getSellingFormatTemplate()->getBestOfferAcceptSource();

        $price = 0;
        switch ($src['mode']) {
            case Ess_M2ePro_Model_SellingFormatTemplates::BEST_OFFER_ACCEPT_MODE_PERCENTAGE:
                $price = $this->getBuyItNowPrice() * (float)$src['value'] / 100;
                break;

            case Ess_M2ePro_Model_SellingFormatTemplates::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE:
                $price = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;
        }

        return round($price, 2);
    }

    public function getBestOfferRejectPrice()
    {
        if (!$this->isListingTypeFixed()) {
            return 0;
        }

        if (!$this->getSellingFormatTemplate()->isBestOfferEnabled()) {
            return 0;
        }

        if ($this->getSellingFormatTemplate()->isBestOfferRejectModeNo()) {
            return 0;
        }

        $src = $this->getSellingFormatTemplate()->getBestOfferRejectSource();

        $price = 0;
        switch ($src['mode']) {
            case Ess_M2ePro_Model_SellingFormatTemplates::BEST_OFFER_REJECT_MODE_PERCENTAGE:
                $price = $this->getBuyItNowPrice() * (float)$src['value'] / 100;
                break;

            case Ess_M2ePro_Model_SellingFormatTemplates::BEST_OFFER_REJECT_MODE_ATTRIBUTE:
                $price = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;
        }

        return round($price, 2);
    }

    //-------------------------------

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

        $mainImage = array($mainImage);

        $limitGalleryImages = $this->getDescriptionTemplate()->getGalleryImagesMode();

        if ($limitGalleryImages <= 0) {
            return $mainImage;
        }

        $galleryImages = $this->getMagentoProduct()->getGalleryImagesLinks($limitGalleryImages+1);
        $galleryImages = array_unique($galleryImages);

        if (count($galleryImages) <= 0) {
            return $mainImage;
        }

        if (in_array($mainImage[0],$galleryImages)) {

            $tempGalleryImages = array();
            foreach ($galleryImages as $tempImage) {
                if ($mainImage[0] == $tempImage) {
                    continue;
                }
                $tempGalleryImages[] = $tempImage;
            }
            $galleryImages = $tempGalleryImages;
        }

        $galleryImages = array_slice($galleryImages,0,$limitGalleryImages);
        
        return array_merge($mainImage, $galleryImages);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_ListingsTemplatesShippings[]
     */
    public function getLocalShippingMethods()
    {
        $returns = array();

        $items = $this->getListingTemplate()->getListingsTemplatesShippings(true);
        foreach ($items as $item) {
            if ($item->isShippingTypeLocal()) {
                $item->setMagentoProduct($this->getMagentoProduct());
                $returns[] = $item;
            }
        }

        return $returns;
    }

    /**
     * @return Ess_M2ePro_Model_ListingsTemplatesShippings[]
     */
    public function getInternationalShippingMethods()
    {
        $returns = array();

        $items = $this->getListingTemplate()->getListingsTemplatesShippings(true);
        foreach ($items as $item) {
            if (!$item->isShippingTypeInternational()) {
                $item->setMagentoProduct($this->getMagentoProduct());
                $returns[] = $item;
            }
        }

        return $returns;
    }

    /**
     * @return Ess_M2ePro_Model_ListingsTemplatesSpecifics[]
     */
    public function getListingsTemplatesSpecifics()
    {
        $returns = array();

        $items = $this->getListingTemplate()->getListingsTemplatesSpecifics(true);
        foreach ($items as $item) {
            $item->setMagentoProduct($this->getMagentoProduct());
            $returns[] = $item;
        }

        return $returns;
    }

    //-------------------------------

    public function getPackageSize()
    {
        $src = $this->getListingTemplate()->getCalculatedShipping()->getPackageSizeSource();

        if ($src['mode'] == Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::PACKAGE_SIZE_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getDimensions()
    {
        $src = $this->getListingTemplate()->getCalculatedShipping()->getDimensionsSource();

        if ($src['mode'] == Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::DIMENSIONS_NONE) {
            return array();
        }

        if ($src['mode'] == Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::DIMENSIONS_CUSTOM_ATTRIBUTE) {
            return array(
                'width' => $this->getMagentoProduct()->getAttributeValue($src['width_attribute']),
                'height' => $this->getMagentoProduct()->getAttributeValue($src['height_attribute']),
                'depth' => $this->getMagentoProduct()->getAttributeValue($src['depth_attribute'])
            );
        }

        return array(
            'width' => $src['width_value'],
            'height' => $src['height_value'],
            'depth' => $src['depth_value']
        );
    }

    public function getWeight()
    {
        $src = $this->getListingTemplate()->getCalculatedShipping()->getWeightSource();

        if ($src['mode'] == Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::WEIGHT_CUSTOM_ATTRIBUTE) {

            $weight_value = $this->getMagentoProduct()->getAttributeValue($src['weight_attribute']);
            $weight_value = str_replace(",", ".", $weight_value);
            $weight_array = explode(".", $weight_value);

            $minor = $major = 0;
            if (count($weight_array) >= 2) {
                $major = (int)$weight_array[0];
                $minor = (int)rtrim($weight_array[1], '0');

                if ($minor > 0 && $this->getListingTemplate()->getCalculatedShipping()->isMeasurementSystemEnglish()) {
                    $minor = ($minor / pow(10, strlen($minor))) * 16;
                    $minor = round($minor, 2);
                }

                $minor < 0 && $minor = 0;
            } else {
                $major = $weight_value;
            }

            return array(
                'minor' => (float)$minor,
                'major' => (int)$major
            );
        }

        return array(
            'minor' => (int)$src['weight_minor'],
            'major' => (int)$src['weight_major']
        );
    }

    public function getLocalHandling()
    {
        $src = $this->getListingTemplate()->getCalculatedShipping()->getLocalHandlingSource();

        if ($src['mode'] == Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::HANDLING_NONE) {
            return 0;
        }
        
        if ($src['mode'] == Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::HANDLING_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getInternationalHandling()
    {
        $src = $this->getListingTemplate()->getCalculatedShipping()->getInternationalHandlingSource();

        if ($src['mode'] == Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::HANDLING_NONE) {
            return 0;
        }
        
        if ($src['mode'] == Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::HANDLING_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    // ########################################

    public function isNotListed()
    {
        return $this->getStatus() == self::STATUS_NOT_LISTED;
    }

    public function isListed()
    {
        return $this->getStatus() == self::STATUS_LISTED;
    }

    public function isSold()
    {
        return $this->getStatus() == self::STATUS_SOLD;
    }

    public function isStopped()
    {
        return $this->getStatus() == self::STATUS_STOPPED;
    }

    public function isFinished()
    {
        return $this->getStatus() == self::STATUS_FINISHED;
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

    public function listEbay(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_LIST, $params);
    }

    public function relistEbay(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST, $params);
    }

    public function reviseEbay(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE, $params);
    }

    public function stopEbay(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP, $params);
    }

    //----------------

    protected function processDispatcher($action, array $params = array())
    {
        if (is_null($this->getId())) {
            throw new Exception('Load instance first');
        }

        return Mage::getModel('M2ePro/Connectors_Ebay_Item_Dispatcher')->process($action, $this->getId(), $params);
    }

    // ########################################
}