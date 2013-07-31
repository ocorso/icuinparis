<?php

/*
 * Class for import product data
 *
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Import_Product
{
    public function importProduct($productInfo)
    {
        /** Currency model  */
        $currencyModel = Mage::getModel('directory/currency');
        // Get all base and allowed currencies. Need to convert eBay item price
        // currency to allowed by Magento
        $allowedCurrency = $currencyModel->getConfigAllowCurrencies();
        $baseCurrency = $currencyModel->getConfigBaseCurrencies();
        if (!isset($baseCurrency[0]) && sizeof($baseCurrency) == 0) {
            $baseCurrency = array();
            $baseCurrency[0] = "USD"; // This is default value for base currency
        }

        // Data of new added product
        $simpleProductData = array();

        $simpleProductData['name'] = $productInfo["title"];

        // Calculate product SKU
        if (!isset($productInfo['sku']) || !$productInfo['sku']) {
            // In product information no SKU. Convert from Name
            $skuValue = Mage::helper('M2ePro')->convertStringToSku($productInfo['title']);
        } else {
            $skuValue = substr($productInfo['sku'], 0, 64);
        }

        $resultOfSkuCheck = $this->_getUniqueSku($skuValue, false);
        if (is_array($resultOfSkuCheck)) {
            return $resultOfSkuCheck;
        }

        // String - new sku value
        $simpleProductData['sku'] = $resultOfSkuCheck;

        $product = Mage::getModel('catalog/product');

        $product->setTypeId('simple');

        // Set Attribute Set ID for product
        $attributes = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())->toArray();

        $tempArray = array();
        foreach ($attributes['items'] as $attribute) {
            $tempArray[] = $attribute['attribute_set_name'];
        }
        $defaultKey = array_search('Default', $tempArray);
        if ($defaultKey === false) {
            // can't find default attribute set. Skip product creation
            return false;
        }

        $product->setAttributeSetId($attributes['items'][$defaultKey]['attribute_set_id']);

        // Skip assign categories to product
        // $product->setCategoryIds();

        $uniqueKey = uniqid();
        // Get product images
        $productImageFileName = array();
        if ($productInfo['pictureUrl'] != array()) {
            foreach ($productInfo['pictureUrl'] as $singlePicture) {
                // Download product image from eBay
                $imageFileNameResultDownload = Mage::helper('M2ePro/ProductImport')->downloadImage($singlePicture, $uniqueKey);
                if ($imageFileNameResultDownload['success'] != false) {
                    // Success download
                    $productImageFileName[] = $imageFileNameResultDownload['filename'];
                }
            }
        }

        $_productDescription = $productInfo["title"];

        if ($productInfo["description"] != null && $productInfo["description"] != '') {
            // Remove javascript, style from description
            $_productDescription = Mage::helper('M2ePro/ProductImport')->stripHtmlTags($productInfo["description"]);
        }

        $simpleProductData['description'] = $_productDescription;
        $simpleProductData['short_description'] = $productInfo["title"];

        $productQty = $productInfo['qty']; // QTY

        $simpleProductData['stock_data'] = array(
            'qty' => $productQty,
            'is_in_stock' => 1,
            'use_config_manage_stock' => 1,
            'use_config_min_qty' => 1,
            'use_config_min_sale_qty' => 1,
            'use_config_max_sale_qty' => 1,
            'is_qty_decimal' => 0,
            'use_config_backorders' => 1,
            'use_config_notify_stock_qty' => 1,
        );

        // Convert currency to allowed into magento

        $itemCurrencyAllowed = in_array($productInfo['price_currency'], $allowedCurrency);
        $itemCurrencyIsBase = in_array($productInfo['price_currency'], $baseCurrency);

        $currencyCode = null; // Convert from
        $currencyCodeTo = null; // Convert to
        $needConvertCurrency = false;

        if ($itemCurrencyAllowed && $itemCurrencyIsBase) {
            // No need converation, currency same as base
            $itemPrice = $productInfo['price'];
        } else if ($itemCurrencyAllowed && !$itemCurrencyIsBase) {
            // Need convertation, currency on eBay different that base
            $itemPrice = $productInfo['price'];
            $currencyCode = $baseCurrency[0];
            $currencyCodeTo = $productInfo['price_currency'];
            $needConvertCurrency = true;
        } else if (!$itemCurrencyAllowed) {
            // Original Item price not allowed. Try to use eBay converted
            // Price

            // Set item price as converted price from eBay
            $defaultEbayCurrencyAllow = in_array($productInfo['converted_price_currency'], $allowedCurrency);
            $defaultEbayCurrencyIsBase = in_array($productInfo['converted_price_currency'], $baseCurrency);

            if ($defaultEbayCurrencyAllow && $defaultEbayCurrencyIsBase) {
                $itemPrice = $productInfo['converted_price'];
            } else if ($defaultEbayCurrencyAllow && !$defaultEbayCurrencyIsBase) {
                // eBay converted price allow, not base. Need convert
                $itemPrice = $productInfo['converted_price'];
                $currencyCode = $baseCurrency[0];
                $currencyCodeTo = $productInfo['converted_price_currency'];
                $needConvertCurrency = true;
            } else if (!$defaultEbayCurrencyAllow) {
                $itemPrice = $productInfo['price'];
            }

        }

        if ($needConvertCurrency) {
            $currencyModel->load($currencyCode);
            $convertRate = $currencyModel->getAnyRate($currencyCodeTo);
            $convertRate = ($convertRate == 0) ? 1 : $convertRate;
            // Convert price for equal for currency
            $itemPrice = $itemPrice / $convertRate;
        }

        $simpleProductData['price'] = $itemPrice;

        $simpleProductData['visibility'] = Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;
        $simpleProductData['tax_class_id'] = 0; // 0 - Product Tax Class - NONE
        $simpleProductData['status'] = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;

        // gallery & picture data
        $resultOfGallery = $this->_getProductGalleryArrayInfo($productImageFileName);
        if ($resultOfGallery != false) {
            $simpleProductData['image'] = $simpleProductData['thumbnail'] = $simpleProductData['small_image'] = $resultOfGallery['main'];

            $simpleProductData['media_gallery']['images'] = json_encode($resultOfGallery['images']);
            $simpleProductData['media_gallery']['values'] = json_encode($resultOfGallery['values']);
        }

        $websiteId = 1;
        if ($productInfo['storeId'] != 0) {
            $websiteId = Mage::getModel('core/store')->load($productInfo['storeId'])->getWebsiteId();
        }

        if ($websiteId == 0) {
            // Get default website ID
            $websiteId = Mage::helper('M2ePro/Sales')->getDefaultWebsiteId();
        }

        $product->setWebsiteIDs(array($websiteId));
        $product->setCreatedAt(strtotime('now'));

        $product->addData($simpleProductData);
        $product->save();

        if ($productImageFileName != null && $productImageFileName != array()) {
            // After product is created copy image to media folder
            // Copy product image to destination location
            foreach ($productImageFileName as $singleFile) {
                $result = Mage::helper('M2ePro/ProductImport')->copyImageToProduct($singleFile, $uniqueKey);
                // result can has error message of copy, we skip it on current edition
            }

        }
        Mage::helper('M2ePro/ProductImport')->removeTempDir($uniqueKey);

        return array(
            'id' => $product->getId(),
            'storeId' => ($productInfo['storeId']!=0)?$productInfo['storeId']:Mage::helper('M2ePro/Sales')->getProductFirstStoreId($product)
        );
    }

    protected function _getProductGalleryArrayInfo($imagesList)
    {
        if (!is_array($imagesList)) {
            return false;
        }

        $media_gallery = array(
            'images' => array(),
            'values' => array(),
            'main' => null
        );

        $mainImageToGallery = null;
        $currentPosition = 1;
        foreach ($imagesList as $singleImage) {
            $productImageRelLink = "/m2epro/" . $singleImage;

            if ($mainImageToGallery == null) {
                $mainImageToGallery = $productImageRelLink;
            }
            $imageToGallery = array(
                'file' => $productImageRelLink,
                'label' => '',
                'position' => $currentPosition++,
                'disabled' => 0,
                'removed' => 0
            );
            $media_gallery['images'][] = $imageToGallery;
        }

        $media_gallery['values']['main'] = $media_gallery['values']['image'] = $media_gallery['values']['small_image'] = $media_gallery['values']['thumbnail'] = $mainImageToGallery;

        return $media_gallery;
    }

    /**
     * Check for unique product SKU. If such sku exist found free like SKU_1 or
     * returning false (depending from param $foundFree)
     *
     * @param string $productSku sku for check
     * @param boolean $foundFree if set true try to fild first free sku (sku_1),
     * return false on sku exist
     *
     * @return String|array new sku value, or array with with productId and storeId that use sku
     */
    protected function _getUniqueSku($productSku, $foundFree = false)
    {
        $currentSku = $productSku;
        $counter = 1;
        $productModel = Mage::getModel('catalog/product');
        $singleProduct = $productModel->loadByAttribute('sku', $currentSku);
        if ($foundFree) {
            while ($singleProduct && $singleProduct->getId()) {
                $currentSku = $productSku . "_" . $counter;
                $singleProduct = $productModel->loadByAttribute('sku', $currentSku);
                $counter++;
            }
        } else {
            if ($singleProduct && $singleProduct->getId()) {
                // This SKU is used for product
                return array(
                    'id' => (int)$singleProduct->getId(),
                    'storeId' => Mage::helper('M2ePro/Sales')->getProductFirstStoreId($singleProduct),
                );
            }
        }

        // Sku is Unique
        return (string)$currentSku;
    }

}