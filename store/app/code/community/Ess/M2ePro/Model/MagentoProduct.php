<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_MagentoProduct extends Mage_Core_Model_Abstract
{
    const TYPE_SIMPLE = 'simple';
    const TYPE_CONFIGURABLE = 'configurable';
    const TYPE_BUNDLE = 'bundle';
    const TYPE_GROUPED = 'grouped';

    const GROUPED_PRODUCT_ATTRIBUTE_LABEL = 'Option';
    const THUMBNAIL_IMAGE_CACHE_TIME = 604800;

    // ########################################

    private $_productId = 0;

    private $_storeId = 0;

    /**
     * @var Mage_Catalog_Model_Product
     */
    private $_productModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/MagentoProduct');
    }

    // ########################################

    /**
     * @param int|null $productId
     * @param int|null $storeId
     * @return Ess_M2ePro_Model_MagentoProduct
     */
    public function loadProduct($productId = NULL, $storeId = NULL)
    {
        $productId = (is_null($productId)) ? $this->_productId : $productId;
        $storeId = (is_null($storeId)) ? $this->_storeId : $storeId;

        if ($productId <= 0) {
            throw new Exception('The Product ID is not set.');
        }

        $this->_productModel = Mage::getModel('catalog/product')
                                         ->setStoreId($storeId)
                                         ->load($productId);

        $this->setProductId($productId);
        $this->setStoreId($storeId);

        return $this;
    }

    // ########################################
    
    /**
     * @param int $productId
     * @return Ess_M2ePro_Model_MagentoProduct
     */
    public function setProductId($productId)
    {
        $this->_productId = $productId;
        return $this;
    }

    public function getProductId()
    {
        return $this->_productId;
    }

    // ########################################

    /**
     * @param int $storeId
     * @return Ess_M2ePro_Model_MagentoProduct
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    public function getStoreId()
    {
        return $this->_storeId;
    }

    // ########################################

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if ($this->_productModel) {
            return $this->_productModel;
        }

        if ($this->_productId > 0) {
            $this->loadProduct();
            return $this->_productModel;
        }

        throw new Exception('Load instance first');
    }

    /**
     * @param Mage_Catalog_Model_Product $productModel
     * @return Ess_M2ePro_Model_MagentoProduct
     */
    public function setProduct(Mage_Catalog_Model_Product $productModel)
    {
        $this->_productModel = $productModel;

        $this->setProductId($this->_productModel->getId());
        $this->setStoreId($this->_productModel->getStoreId());

        return $this;
    }

    // ########################################

    public static function getTypeIdByProductId($productId)
    {
        $productId = (int)$productId;
        $table  = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');

        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                             ->select()
                             ->from($table,'type_id')
                             ->where('`entity_id` = ?',(int)$productId);

        return Mage::getModel('Core/Mysql4_Config')
                        ->getReadConnection()
                        ->fetchOne($dbSelect);
    }
    
    public static function getNameByProductId($productId, $storeId = 0)
    {
        // Prepare tables names
        //-----------------------------
        $catalogProductEntityVarCharTable  = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar');
        $eavAttributeTable  = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
        //-----------------------------

        // Make query for select
        //-----------------------------
        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                             ->select()
                             ->from(array('cpev'=>$catalogProductEntityVarCharTable),array('name'=>'value'))
                             ->join(array('ea'=>$eavAttributeTable),'`cpev`.`attribute_id` = `ea`.`attribute_id` AND `ea`.`attribute_code` = \'name\'',array())
                             ->where('`cpev`.`entity_id` = ?',(int)$productId)
                             ->where('`cpev`.`store_id` = ?',(int)$storeId);
        //-----------------------------

        // Get row of product name
        //-----------------------------
        $name = Mage::getModel('Core/Mysql4_Config')
                        ->getReadConnection()
                        ->fetchOne($dbSelect);
        //-----------------------------

        if ($name) {
            return $name;
        }

        if ($storeId == 0) {
            return '';
        }

        // Make query for select
        //-----------------------------
        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                             ->select()
                             ->from(array('cpev'=>$catalogProductEntityVarCharTable),array('name'=>'value'))
                             ->join(array('ea'=>$eavAttributeTable),'`cpev`.`attribute_id` = `ea`.`attribute_id` AND `ea`.`attribute_code` = \'name\'',array())
                             ->where('`cpev`.`entity_id` = ?',(int)$productId)
                             ->where('`cpev`.`store_id` = 0');
        //-----------------------------

        // Get row of product name
        //-----------------------------
        $name = Mage::getModel('Core/Mysql4_Config')
                        ->getReadConnection()
                        ->fetchOne($dbSelect);
        //-----------------------------

        if ($name) {
            return $name;
        }

        return '';
    }

    public static function getSkuByProductId($productId)
    {
        // Prepare tables names
        //-----------------------------
        $catalogProductEntityTable  = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');
        //-----------------------------

        // Make query for select
        //-----------------------------
        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                             ->select()
                             ->from($catalogProductEntityTable,'sku')
                             ->where('`entity_id` = ?',(int)$productId);
        //-----------------------------

        // Get row of product sku
        //-----------------------------
        $name = Mage::getModel('Core/Mysql4_Config')
                        ->getReadConnection()
                        ->fetchOne($dbSelect);
        //-----------------------------

        return $name;
    }

    public static function getQtyByProductId($productId)
    {
        return (int)Mage::getModel('cataloginventory/stock_item')
                        ->loadByProduct($productId)
                        ->getQty();
    }

    public static function getStockAvailabilityByProductId($productId)
    {
        return (int)Mage::getModel('cataloginventory/stock_item')
                        ->loadByProduct($productId)
                        ->getIsInStock();
    }

    public static function getStatusByProductId($productId, $storeId = 0)
    {
        $status = Mage::getModel('catalog/product_status')
                        ->getProductStatus($productId,$storeId);
        
        if (is_array($status) && isset($status[$productId])) {
            $status = (int)$status[$productId];
        } else {
            $status = 0;
        }
        
        return $status;
    }

    // ########################################

    public function getTypeId()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            return self::getTypeIdByProductId($this->_productId);
        }
        return $this->getProduct()->getTypeId();
    }

    public function getName()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            return self::getNameByProductId($this->_productId, $this->_storeId);
        }
        return $this->getProduct()->getName();
    }

    public function getSku()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            $temp = self::getSkuByProductId($this->_productId);
            if (!is_null($temp) && $temp != '') {
                return $temp;
            }
        }
        return $this->getProduct()->getSku();
    }
    
    //-----------------------------------------

    public function isSimpleType()
    {
        return $this->getTypeId() == self::TYPE_SIMPLE;
    }

    public function isConfigurableType()
    {
        return $this->getTypeId() == self::TYPE_CONFIGURABLE;
    }

    public function isBundleType()
    {
        return $this->getTypeId() == self::TYPE_BUNDLE;
    }

    public function isGroupedType()
    {
        return $this->getTypeId() == self::TYPE_GROUPED;
    }

    //-----------------------------------------

    public function isVariationOnlyType()
    {
        return $this->isConfigurableType() || $this->isBundleType() || $this->isGroupedType();
    }

    public function isSimpleTypeWithCustomOptions()
    {
        if (!$this->isSimpleType()) {
            return false;
        }
        if (count($this->getProduct()->getOptions()) > 0) {
            return true;
        }
        return false;
    }

    public function isSimpleTypeWithoutCustomOptions()
    {
        if (!$this->isSimpleType()) {
            return false;
        }
        
        return !$this->isSimpleTypeWithCustomOptions();
    }

    //-----------------------------------------

    public function getPrice()
    {
        return (double)$this->getProduct()->getPrice();
    }

    public function setPrice($value)
    {
        return $this->getProduct()->setPrice($value);
    }

    //-----------------------------------------

    public function getSpecialPrice()
    {
        $fromDate = $this->getProduct()->getSpecialFromDate();
        $toDate = $this->getProduct()->getSpecialToDate();

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        if (is_null($fromDate) || $fromDate === false || $fromDate == '') {
            $fromDate = $currentTimeStamp - 3600*24*30*12;
        } else {
            $fromDate = strtotime($fromDate);
        }

        if (is_null($toDate) || $toDate === false || $toDate == '') {
            $toDate = $currentTimeStamp + 3600*24*30*12;
        } else {
            $toDate = strtotime($toDate) + 3600*24;
        }

        if ($currentTimeStamp < $fromDate || $currentTimeStamp > $toDate) {
            return 0;
        }

        return (double)$this->getProduct()->getSpecialPrice();
    }

    public function setSpecialPrice($value)
    {
        return $this->getProduct()->setSpecialPrice($value);
    }

    //-----------------------------------------

    public function getQty()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            return self::getQtyByProductId($this->_productId);
        }
        return (int)Mage::getModel('cataloginventory/stock_item')
                            ->loadByProduct($this->getProduct())
                            ->getQty();
    }

    public function setQty($value)
    {
        Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($this->getProduct())
                ->setQty($value)
                ->save();
    }

    //-----------------------------------------

    public function getStockAvailability()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            $temp = self::getStockAvailabilityByProductId($this->_productId);
            if ($temp == 0 || $temp == 1) {
                return $temp;
            }
        }
        return (int)$this->getProduct()->getStockItem()->getIsInStock();
    }
    
    public function getStatus()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            $temp = self::getStatusByProductId($this->_productId, $this->_storeId);
            if ($temp == Mage_Catalog_Model_Product_Status::STATUS_DISABLED ||
                $temp == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                return $temp;
            }
        }
        return (int)$this->getProduct()->getStatus();
    }

    //-----------------------------------------

    public function getAttributeValue($attributeCode)
    {
        $productObject = $this->getProduct();

        $value = $productObject->getData($attributeCode);
        if (is_null($value) || is_bool($value) || is_array($value) || $value === '') {
            return '';
        }

        /** @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
        $attribute = $productObject->getResource()->getAttribute($attributeCode);

        // SELECT and MULTISELECT
        if ($attribute->getFrontendInput() === 'select' || $attribute->getFrontendInput() === 'multiselect') {

            // User Attribute
            if ((int)$attribute->getData('is_user_defined') == 1) {
                
                $valueNew = '';
                $optionIds = (array)explode(',',$value);

                foreach ($optionIds as $optionId) {
                    $attributeOption = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                                    ->addFieldToFilter('main_table.option_id', array('in' => $optionId))
                                                    //->setIdFilter($optionId)
                                                    ->setStoreFilter($this->getStoreId())
                                                    ->load()->getFirstItem();
                    $valueNew != '' && $valueNew .= ', ';
                    $valueNew .= $attributeOption->getData('value');
                }

                $value = (string)$valueNew;
            }
            
        // DATE
        } else if ($attribute->getFrontendInput() == 'date') {
            $temp = explode(' ',$value);
            isset($temp[0]) && $value = (string)$temp[0];

        // YES NO
        }  else if ($attribute->getFrontendInput() == 'boolean') {
            (bool)$value ? $value = Mage::helper('M2ePro')->__('Yes') :
                           $value = Mage::helper('M2ePro')->__('No');
            
        // PRICE
        }  else if ($attribute->getFrontendInput() == 'price') {
            $value = (string)round($value, 2);

        // MEDIA IMAGE
        }  else if ($attribute->getFrontendInput() == 'media_image') {
            if ($value == 'no_selection') {
                $value = '';
            } else {
                if (!preg_match('((mailto\:|(news|(ht|f)tp(s?))\://){1}\S+)',$value)) {
                    $value = Mage::getSingleton('catalog/product_media_config')->getMediaUrl($value);
                    $value = str_replace('https://','http://',$value);
                }
            }
        }

        return is_string($value) ? $value : '';
    }

    //-----------------------------------------

    public function getThumbnailImageLink()
    {
        $eaTable = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
        $cpevTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar');

        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                              ->select()
                              ->from(array('cpev'=>$cpevTable),'value')
                              ->joinInner(array('ea'=>$eaTable),'`ea`.`attribute_id` = `cpev`.`attribute_id`',array())
                              ->where('`cpev`.`store_id` = ?',(int)$this->getStoreId())
                              ->where('`cpev`.`entity_id` = ?',(int)$this->getProductId())
                              ->where('`ea`.`attribute_code` = \'thumbnail\'');

        $tempPath = (string)Mage::getModel('Core/Mysql4_Config')->getReadConnection()->fetchOne($dbSelect);

        if ($tempPath == '' || $tempPath == 'no_selection' || $tempPath == '/') {

			$dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
								  ->select()
								  ->from(array('cpev'=>$cpevTable),'value')
								  ->joinInner(array('ea'=>$eaTable),'`ea`.`attribute_id` = `cpev`.`attribute_id`',array())
								  ->where('`cpev`.`store_id` = ?',0)
								  ->where('`cpev`.`entity_id` = ?',(int)$this->getProductId())
								  ->where('`ea`.`attribute_code` = \'thumbnail\'');

			$tempPath = (string)Mage::getModel('Core/Mysql4_Config')->getReadConnection()->fetchOne($dbSelect);

			if ($tempPath == '' || $tempPath == 'no_selection' || $tempPath == '/') {
				return NULL;
			}
        }

        $imagePathOriginal = Mage::getBaseDir('media').DS.'catalog/product'.$tempPath;

        if (!is_file($imagePathOriginal)) {
            return NULL;
        }

        $width = 100;
        $height = 100;

        $prefixResizedImage = 'resized-'.$width.'px-'.$height.'px-';
        $imagePathResized = dirname($imagePathOriginal).DS.$prefixResizedImage.basename($imagePathOriginal);

        if (is_file($imagePathResized)) {
            $currentTime = Mage::helper('M2ePro')->getCurrentGmtDate(true);
            if (filemtime($imagePathResized) + self::THUMBNAIL_IMAGE_CACHE_TIME > $currentTime) {
                return Mage::getSingleton('catalog/product_media_config')->getMediaUrl(
                    str_replace(basename($imagePathOriginal),$prefixResizedImage.basename($imagePathOriginal),$tempPath)
                );
            }
            @unlink($imagePathResized);
        }

        try {

            $imageObj = new Varien_Image($imagePathOriginal);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->keepFrame(FALSE);
            $imageObj->resize($width, $height);
            $imageObj->save($imagePathResized);

        } catch (Exception $exception) {
            return NULL;
        }

        if (!is_file($imagePathResized)) {
            return NULL;
        }

        return Mage::getSingleton('catalog/product_media_config')->getMediaUrl(
            str_replace(basename($imagePathOriginal),$prefixResizedImage.basename($imagePathOriginal),$tempPath)
        );
    }

    public function getImageLink($attribute = 'image')
    {
        if ($attribute == '') {
            return '';
        }

        $image = $this->getAttributeValue($attribute);

        if ($image != '') {
            $image = str_replace('https://','http://',$image);
        }
        
        return $image;
    }

    public function getGalleryImagesLinks($limitImages = 0)
    {
        $limitImages = (int)$limitImages;
        $limitImages <= 0 && $limitImages = 0;
        
        if ($limitImages == 0) {
            return array();
        }

        $galleryImages = $this->getProduct()->getData('media_gallery');

        if (!isset($galleryImages['images'])) {
            return array();
        }

        $images = array();

        $i = 0;
        foreach ($galleryImages['images'] as $galleryImage) {
            
            if ($i >= $limitImages) {
                break;
            }

            if ((bool)$galleryImage['disabled']) {
                continue;
            }

            $imageUrl = Mage::getSingleton('catalog/product_media_config')->getMediaUrl($galleryImage['file']);
            $images[] = str_replace('https://','http://',$imageUrl);
            $i++;
        }

        return $images;
    }

    // ########################################

    public function getProductVariations()
    {
        // Simple options
        $totalTitles = array();
        $totalList = array();

        if ($this->isSimpleType()) {
            $customOptionsVariationsInfo = $this->_getCustomOptionsForVariation();
            $totalTitles = array_merge($totalTitles, $customOptionsVariationsInfo['title']);
            $totalList = array_merge($totalList, $customOptionsVariationsInfo['list']);
        }

        if ($this->isBundleType()) {
            $bundleOptionsVariationsInfo = $this->_getBundleOptionsForVariation();
            $totalTitles = array_merge($totalTitles, $bundleOptionsVariationsInfo['title']);
            $totalList = array_merge($totalList, $bundleOptionsVariationsInfo['list']);
        }

        if ($this->isGroupedType()) {
            $groupedOptionsVariationsInfo = $this->_getGroupedOptionsForVariation();
            $totalTitles = array_merge($totalTitles, $groupedOptionsVariationsInfo['title']);
            $totalList = array_merge($totalList, $groupedOptionsVariationsInfo['list']);
        }

        $combinations = array();
        $this->_arrayCombination($combinations, $totalList);

        if ($this->isConfigurableType()) {
            // another structure of work for configurable products
            // not need call recursive function
            $configurableOptionsVariationsInfo = $this->_getConfigurableOptionsForVariation();
            $combinations = array_merge($combinations, $configurableOptionsVariationsInfo['variations']);
            $totalTitles = array_merge($totalTitles, $configurableOptionsVariationsInfo['title']);
        }

        // titles validation, not more 30 titles required
        foreach ($totalTitles as $titleList) {
            if (count($titleList) > 30) {
                // Maximum 30 options: Color: Red, Blue, Green...
                return array(
                    'set' => array(),
                    'variation' => array()
                );
            }
        }

        $variations = array();
        foreach ($combinations as $singleVariation) {
            $specificsList = array();
            foreach ($singleVariation as $variationOption) {
                $specificsList[] = array(
                    'product_id' => $variationOption['product_id'],
                    'product_type' => $variationOption['product_type'],
                    'attribute' => $variationOption['attribute'],
                    'option' => $variationOption['option'],
                );
            }
            if (count($specificsList) > 5) {
                // Max 5 pair attribute-option: Color: Blue, Size: S, ...
                return array(
                    'set' => array(),
                    'variation' => array()
                );
            }
            $variations[] = array(
                'specifics' => $specificsList
            );
        }
        
        if (count($variations) > 120) {
            // Not more that 120 possible combination
            return array(
                'set' => array(),
                'variation' => array()
            );
        }

        return array(
            'set' => $totalTitles,
            'variation' => $variations,
        );
    }

    //-----------------------------------------
    
    protected function _getCustomOptionsForVariation()
    {
        $product = $this->getProduct();

        $variationOptionsTitle = array();
        $variationOptionsList = array();
        
        foreach ($product->getOptions() as $productCustomOptions) {

            if (!(bool)(int)$productCustomOptions->getData('is_require')) {
                continue;
            }

            if (in_array($productCustomOptions->getType(), array('drop_down', 'radio', 'multiple', 'checkbox'))) {

                $optionCombinationTitle = array();
                $possibleVariationProductOptions = array();
                foreach ($productCustomOptions->getValues() as $option) {
                    $optionCombinationTitle[] = $option->getTitle();
                    $possibleVariationProductOptions[] = array(
                        'product_id' => $product->getId(),
                        'product_type' => $product->getTypeId(),
                        'attribute' => $productCustomOptions->getTitle(),
                        'option' => $option->getTitle()
                    );
                }

                $variationOptionsTitle[$productCustomOptions->getTitle()] = $optionCombinationTitle;
                $variationOptionsList[] = $possibleVariationProductOptions;
            }
        }

        return array(
            'title' => $variationOptionsTitle,
            'list' => $variationOptionsList
        );
    }

    protected function _getBundleOptionsForVariation()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_BUNDLE) {
            // Not bundle product, return nothing
            return array(
                'title' => array(),
                'list' => array()
            );
        }
        
        $productInstance = $product->getTypeInstance(true);
        $optionCollection = $productInstance->getOptionsCollection($product);

        $variationOptionsTitle = array();
        $variationOptionsList = array();

        foreach ($optionCollection as $singleOption) {

            if (!(bool)(int)$singleOption->getData('required')) {
                continue;
            }

            $optionCombinationTitle = array();
            $possibleVariationProductOptions = array();

            $selectionsCollectionItems = $productInstance->getSelectionsCollection(array(0 => $singleOption->getId()), $product)->getItems();

            foreach ($selectionsCollectionItems as $item) {
                $optionCombinationTitle[] = $item->getName();
                $possibleVariationProductOptions[] = array(
                    'product_id' => $item->getProductId(),
                    'product_type' => $product->getTypeId(),
                    'attribute' => $singleOption->getDefaultTitle(),
                    'option' => $item->getName()
                );
            }
            
            $variationOptionsTitle[$singleOption->getDefaultTitle()] = $optionCombinationTitle;
            $variationOptionsList[] = $possibleVariationProductOptions;
        }

        return array(
            'title' => $variationOptionsTitle,
            'list' => $variationOptionsList
        );
    }

    protected function _getConfigurableOptionsForVariation()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_CONFIGURABLE) {
            // Not configurable product, return nothing
            return array(
            );
        }

        $productTypeInstance = $product->getTypeInstance();

        $configurableProducts = $productTypeInstance->getUsedProducts(null, $product);

        $possibleVariationProductOptions = array();
        $variationOptionsTitle = array();

        foreach ($configurableProducts as $item) {

            // get product depended information
            $specifics = array();

            foreach ($productTypeInstance->getUsedProductAttributes() as $attribute) {
                
                $attributeLabel = ucfirst($item->getResource()->getAttribute($attribute->getAttributeCode())->getFrontendLabel());
                $attributeValue = Mage::getModel('M2ePro/MagentoProduct')
                                            ->setProduct($item)
                                            ->getAttributeValue($attribute->getAttributeCode());

                $specifics[] = array(
                    'product_id' => $item->getId(),
                    'product_type' => self::TYPE_CONFIGURABLE,
                    'attribute' => $attributeLabel,
                    'option' => $attributeValue
                );
                
                // Generate list of all options titles
                if (!isset($variationOptionsTitle[$attributeLabel])) {
                    $variationOptionsTitle[$attributeLabel] = array();
                }

                if (!in_array($attributeValue, $variationOptionsTitle[$attributeLabel])) {
                    $variationOptionsTitle[$attributeLabel][] = $attributeValue;
                }
            }
            
            $possibleVariationProductOptions[] = $specifics;
        }

        return array(
          'title' => $variationOptionsTitle,
          'variations' => $possibleVariationProductOptions
        );
    }

    protected function _getGroupedOptionsForVariation()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_GROUPED) {
            // Not bundle product, return nothing
            return array(
                'title' => array(),
                'list' => array()
            );
        }

        $optionCombinationTitle = array();

        $possibleVariationProductOptions = array();
        $associatedProducts = $product->getTypeInstance()->getAssociatedProducts();

        foreach ($associatedProducts as $singleProduct) {
            $optionCombinationTitle[] = $singleProduct->getName();
            $possibleVariationProductOptions[] = array(
                'product_id' => $singleProduct->getId(),
                'product_type' => $product->getTypeId(),
                'attribute' => self::GROUPED_PRODUCT_ATTRIBUTE_LABEL,
                'option' => $singleProduct->getName()
            );
        }
        
        $variationOptionsTitle[self::GROUPED_PRODUCT_ATTRIBUTE_LABEL] = $optionCombinationTitle;
        $variationOptionsList[] = $possibleVariationProductOptions;

        return array(
            'title' => $variationOptionsTitle,
            'list' => $variationOptionsList
        );
    }

    //-----------------------------------------

    /**
     * Recursive function for generate all possible options combination
     *
     * @param array $combinations
     * @param array $optionsList
     * @param array $currentCombination
     * @param int $currentIndex
     * @param int $currentSubIndex
     * @return bool
     */
    protected function _arrayCombination(&$combinations, $optionsList, array $currentCombination = array(), $currentIndex = 0, $currentSubIndex = 0)
    {
        if (!isset($optionsList[$currentIndex])) {
            // We have prepared combination
            if ($currentCombination != array()) {
                $combinations[] = $currentCombination;
            }
            return false;
        }

        if (!isset($optionsList[$currentIndex][$currentSubIndex])) {
            return false;
        }

        $currentCombinationForNextLevel = $currentCombination;
        $currentCombinationForNextLevel[] = $optionsList[$currentIndex][$currentSubIndex];

        $result = $this->_arrayCombination($combinations, $optionsList, $currentCombinationForNextLevel, $currentIndex + 1, 0); // next level
        if ($result == false) {
            $result = $this->_arrayCombination($combinations, $optionsList, $currentCombination, $currentIndex, $currentSubIndex + 1); // next sub level
        }

        return $result;
    }

    // ########################################

    public function getProductVariationsForOrder()
    {
        if ($this->isSimpleType()) {
            return $this->_getCustomOptionsForOrder();
        }

        if ($this->isBundleType()) {
            return $this->_getBundleOptionsForOrder();
        }

        if ($this->isGroupedType()) {
            return $this->_getGroupedOptionsForOrder();
        }

        if ($this->isConfigurableType()) {
            return $this->_getConfigurableOptionsForOrder();
        }

        return array();
    }

    //-----------------------------------------

    protected function _getCustomOptionsForOrder()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_SIMPLE) {
            return array();
        }

        $customOptions = array();

        $productOptions = $product->getOptions();

        foreach ($productOptions as $option) {
            if (!(bool)(int)$option->getData('is_require')) {
                continue;
            }

            $customOption = array(
                'option_id'     => $option->getData('option_id'),
                'store_title'   => $option->getData('store_title'),
                'title'         => $option->getData('title'),
                'default_title' => $option->getData('default_title')
            );

            $values = $option->getValues();

            foreach ($values as $value) {
                $customOption['values'][] = array(
                    'value_id'      => $value->getData('option_type_id'),
                    'store_title'   => $value->getData('store_title'),
                    'title'         => $value->getData('title'),
                    'default_title' => $value->getData('default_title')
                    //'sku'           => $value->getData('sku')
                );
            }

            $customOptions[] = $customOption;
        }

        return $customOptions;
    }

    //-----------------------------------------

    protected function _getBundleOptionsForOrder()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_BUNDLE) {
            return array();
        }

        $bundleOptions = array();

        $optionsCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
        $selectionsCollection = $product->getTypeInstance(true)->getSelectionsCollection($optionsCollection->getAllIds(), $product);

        foreach ($optionsCollection as $option) {
            if (!$option->getData('required')) {
                continue;
            }

            $bundleOption = array(
                'option_id'     => $option->getData('option_id'),
                'default_title' => $option->getData('default_title')
            );

            foreach ($selectionsCollection as $selection) {
                if ($option->getData('option_id') != $selection->getData('option_id')) {
                    continue;
                }

                $bundleOption['values'][] = array(
                    'value_id'  => $selection->getData('selection_id'),
                    'name'       => $selection->getData('name'),
                    'product_id' => $selection->getData('entity_id')
                );
            }

            $bundleOptions[] = $bundleOption;
        }

        return $bundleOptions;
    }

    //-----------------------------------------

    protected function _getGroupedOptionsForOrder()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_GROUPED) {
            return array();
        }

        $associatedProducts = $product->getTypeInstance()->getAssociatedProducts();

        if (count($associatedProducts)) {
            return $associatedProducts;
        }

        return $product->getTypeInstance()->getChildrenIds($product->getId());
    }

    //-----------------------------------------

    protected function _getConfigurableOptionsForOrder()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_CONFIGURABLE) {
            return array();
        }

        // Need this to get default labels
        // ----------------
        $backupId = Mage::app()->getStore()->getId();
        Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
        // ----------------

        $productAttributes = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);

        // ----------------
        Mage::app()->getStore()->setId($backupId);
        // ----------------

        $configurableOptions = array();

        foreach ($productAttributes as $attribute) {

            $configurableOption = array(
                'option_id'      => $attribute['attribute_id'],
                'store_label'    => $attribute['store_label'],
                'frontend_label' => $attribute['frontend_label'],
                'label'          => $attribute['label']
            );

            foreach ($attribute['values'] as $value) {
                $configurableOption['values'][] = array(
                    'value_id'      => $value['value_index'],
                    'store_label'   => $value['store_label'],
                    'default_label' => $value['default_label'],
                    'label'         => $value['label']
                );
            }

            $configurableOptions[] = $configurableOption;
        }

        return $configurableOptions;
    }

    // ########################################
}