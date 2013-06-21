<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Magento_Product extends Mage_Core_Model_Abstract
{
    protected $_account = NULL;

    protected $_data = array();

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Orders_Magento_Product');
    }

    // ########################################

    public function setAccount(Ess_M2ePro_Model_Accounts $account)
    {
        if (is_null($account->getId())) {
            throw new Exception('Account does not exist.');
        }

        $this->_account = $account;

        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Accounts
     */
    public function getAccount()
    {
        if (is_null($this->_account)) {
            throw new Exception('Account was not set.');
        }

        return $this->_account;
    }

    // ########################################

    public function initialize(array $data = array())
    {
        if (!count($data)) {
            throw new Exception('eBay item info is empty.');
        }

        $this->_data['categoryId'] = $data['categoryId'];
        $this->_data['categoryName'] = $data['categoryName'];

        $this->_data['title'] = $data['title'];
        $this->_data['description'] = $data['description'];

        if (!$data['sku']) {
            $this->_data['sku'] = Mage::helper('M2ePro')->convertStringToSku($data['title']);
        } else {
            $this->_data['sku'] = substr($data['sku'], 0, 64);
        }

        $this->_data['qty'] = (int)$data['qty']; // 1 or more

        $this->_data['price'] = (float)$data['price'];
        $this->_data['price_currency'] = $data['price_currency'];

        $this->_data['converted_price'] = (float)$data['converted_price'];
        $this->_data['converted_price_currency'] = $data['converted_price_currency'];

        $this->_data['galleryUrl'] = $data['galleryUrl'];
        $this->_data['photoDisplay'] = $data['photoDisplay'];
        $this->_data['pictureUrl'] = (array)$data['pictureUrl'];

        $this->_data['unique_key'] = uniqid();

        return true;
    }

    // ########################################

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function createProduct()
    {
        if (!is_null($existProduct = $this->getProductBySku())) {
            return $existProduct;
        }

        // --------
        /** @var $newProduct Mage_Catalog_Model_Product */
        $newProduct = Mage::getModel('catalog/product');
        $newProductData = array();
        // --------

        // --------
        $newProduct->setTypeId('simple');
        $newProduct->setAttributeSetId(Mage::getModel('catalog/product')->getDefaultAttributeSetId());
        // --------

        // --------
        $newProductData['name'] = $this->getData('title');
        $newProductData['description'] = $this->getData('description') ? Mage::helper('M2ePro/ProductImport')->stripHtmlTags($this->getData('description')) : $this->getData('title');
        $newProductData['short_description'] = $this->getData('title');
        $newProductData['sku'] = $this->getData('sku');
        // --------

        // --------
        $newProductData['stock_data'] = array(
            'qty'                         => $this->getData('qty'),
            'is_in_stock'                 => 1,
            'use_config_manage_stock'     => 1,
            'use_config_min_qty'          => 1,
            'use_config_min_sale_qty'     => 1,
            'use_config_max_sale_qty'     => 1,
            'is_qty_decimal'              => 0,
            'use_config_backorders'       => 1,
            'use_config_notify_stock_qty' => 1
        );
        // --------

        // --------
        $newProductData['price'] = $this->getNewProductPrice();

        $newProductData['visibility'] = Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;
        $newProductData['tax_class_id'] = 0; // 0 - Product Tax Class - NONE
        $newProductData['status'] = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
        // --------

        // --------
        $websiteId = 1;

        if ($storeId = $this->getAccount()->getData('orders_listings_store_id')) {
            $websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();

            !$websiteId && $websiteId = Mage::helper('M2ePro/Sales')->getDefaultWebsiteId();
        }

        $newProduct->setWebsiteIDs(array($websiteId));
        // --------

        // --------
        $tempDownloadImages = $this->getNewProductDownloadedImages();
        $newProductGalleryImages = $this->makeProductGallery($tempDownloadImages);

        if (count($newProductGalleryImages)) {
            if (!is_null($newProductGalleryImages['main'])) {
                $newProductData['image'] = $newProductData['thumbnail'] = $newProductData['small_image'] = $newProductGalleryImages['main'];
            }

            count($newProductGalleryImages['images']) && $newProductData['media_gallery']['images'] = json_encode($newProductGalleryImages['images']);
            count($newProductGalleryImages['values']) && $newProductData['media_gallery']['values'] = json_encode($newProductGalleryImages['values']);
        }
        // --------

        // --------
        $newProduct->setCreatedAt(strtotime('now'));
        $newProduct->addData($newProductData);
        $newProduct->save();
        // --------

        // --------
        if (count($tempDownloadImages)) {
            foreach ($tempDownloadImages as $image) {
                Mage::helper('M2ePro/ProductImport')->copyImageToProduct($image, $this->getData('unique_key'));
            }
        }

        Mage::helper('M2ePro/ProductImport')->removeTempDir($this->getData('unique_key'));
        // --------

        return $newProduct;
    }

    // ########################################

    private function getProductBySku()
    {
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $this->getData('sku'));

        if ($product && $product->getId()) {
            return $product;
        }

        return null;
    }

    // ########################################

    private function getNewProductDownloadedImages()
    {
        $productImages = array();

        foreach ($this->_data['pictureUrl'] as $pictureUrl) {
            $downloadedImage = Mage::helper('M2ePro/ProductImport')->downloadImage($pictureUrl, $this->getData('unique_key'));

            if ($downloadedImage['success']) {
                $productImages[] = $downloadedImage['filename'];
            }
        }

        return $productImages;
    }

    private function makeProductGallery($imagesList)
    {
        if (!is_array($imagesList) || !count($imagesList)) {
            return array();
        }

        $media_gallery = array(
            'images' => array(),
            'values' => array(),
            'main' => null
        );

        $mainImageToGallery = null;
        $currentPosition = 1;
        foreach ($imagesList as $singleImage) {
            $productImageRelLink = '/m2epro/' . $singleImage;

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

    // ########################################

    private function getNewProductPrice()
    {
        // TODO: refactor

        /** @var $currencyModel Mage_Directory_Model_Currency */
        $currencyModel = Mage::getModel('directory/currency');

        // Get all base and allowed currencies. Need to convert eBay item price
        // currency to allowed by Magento
        $allowedCurrency = $currencyModel->getConfigAllowCurrencies();
        $baseCurrency = $currencyModel->getConfigBaseCurrencies();

        if (!count($baseCurrency)) {
            $baseCurrency = array('USD');
        }

        $itemCurrencyAllowed = in_array($this->getData('price_currency'), $allowedCurrency);
        $itemCurrencyIsBase = in_array($this->getData('price_currency'), $baseCurrency);

        $defaultEbayCurrencyAllow = in_array($this->getData('converted_price_currency'), $allowedCurrency);
        $defaultEbayCurrencyIsBase = in_array($this->getData('converted_price_currency'), $baseCurrency);

        if ($itemCurrencyAllowed && $itemCurrencyIsBase) {
            return $this->getData('price');
        }

        if (!$itemCurrencyAllowed) {
            if (!$defaultEbayCurrencyAllow) {
                return $this->getData('price');
            }

            if ($defaultEbayCurrencyIsBase) {
                return $this->getData('converted_price');
            }
        }

        $currencyCode = $baseCurrency[0]; // Convert from
        $currencyCodeTo = null; // Convert to

        if ($itemCurrencyAllowed) {
            $itemPrice = $this->getData('price');
            $currencyCodeTo = $this->getData('price_currency');
        } else {
            $itemPrice = $this->getData('converted_price');
            $currencyCodeTo = $this->getData('converted_price_currency');
        }

        $currencyModel->load($currencyCode);
        $convertRate = $currencyModel->getAnyRate($currencyCodeTo);
        $convertRate = ($convertRate == 0) ? 1 : $convertRate;

        // Convert price for equal for currency
        $itemPrice = $itemPrice / $convertRate;

        return $itemPrice;
    }

    // ########################################
}