<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_Item_Helper extends Mage_Core_Model_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Connectors_Ebay_Item_Helper');
    }

    // ########################################

    public function getListRequestData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array $params = array())
    {
        // Set permissions
        //-----------------
        $permissions = array(
            'general'=>true,
            'variations'=>true,
            'qty'=>true,
            'price'=>true,
            'title'=>true,
            'subtitle'=>true,
            'description'=>true
        );

        if (isset($params['only_data'])) {
            foreach ($permissions as &$value) {
                $value = false;
            }
            $permissions = array_merge($permissions,$params['only_data']);
        }

        if (isset($params['all_data'])) {
            foreach ($permissions as &$value) {
                $value = true;
            }
        }
        //-----------------

        $requestData = array();

        // Prepare Variations
        //-------------------
        Mage::helper('M2ePro/Variations')->updateVariations($listingProduct);
        $tempVariations = Mage::getModel('M2ePro/Connectors_Ebay_Item_HelperVariations')
                                                ->getRequestData($listingProduct);

        $requestData['is_variation_item'] = false;
        if (is_array($tempVariations) && count($tempVariations) > 0) {
            $requestData['is_variation_item'] = true;
        }
        //-------------------

        // Get Variations
        //-------------------
        if ($permissions['variations'] && $requestData['is_variation_item']) {

            $requestData['variation'] = $tempVariations;
            $requestData['variation_image'] = Mage::getModel('M2ePro/Connectors_Ebay_Item_HelperVariations')
                                                    ->getImagesData($listingProduct);
            if (count($requestData['variation_image']) == 0) {
                unset($requestData['variation_image']);
            }
        }
        //-------------------

        // Get General Info
        //-------------------
        $permissions['general'] && $requestData['sku'] = $listingProduct->getSku();
        $permissions['general'] && $this->addSellingFormatData($listingProduct,$requestData);
        
        $this->addDescriptionData($listingProduct,$requestData,$permissions);

        if (($permissions['qty'] || $permissions['price']) && !$requestData['is_variation_item']) {
            $this->addQtyPriceData($listingProduct,$requestData,$permissions);
        }

        if ($permissions['general']) {

            $this->addCategoriesData($listingProduct,$requestData);
            $this->addStoreCategoriesData($listingProduct,$requestData);

            $this->addBestOfferData($listingProduct,$requestData);
            $this->addProductDetailsData($listingProduct,$requestData);
            
            $this->addItemSpecificsData($listingProduct,$requestData);
            $this->addAttributeSetData($listingProduct,$requestData);

            $requestData['item_condition'] = $listingProduct->getItemCondition();
            $requestData['listing_enhancements'] = $listingProduct->getListingTemplate()->getEnhancements();
        }
        //-------------------

        // Get Shipping Info
        //-------------------
        if ($permissions['general']) {

            $this->addShippingData($listingProduct,$requestData);

            $requestData['country'] = $listingProduct->getListingTemplate()->getCountry();
            $requestData['postal_code'] = $listingProduct->getListingTemplate()->getPostalCode();
            $requestData['address'] = $listingProduct->getListingTemplate()->getAddress();
        }
        //-------------------

        // Get Payment Info
        //-------------------
        if ($permissions['general']) {

            $this->addPaymentData($listingProduct,$requestData);

            $requestData['vat_percent'] = $listingProduct->getListingTemplate()->getVatPercent();
            $requestData['use_tax_table'] = $listingProduct->getListingTemplate()->isUseEbayTaxTableEnabled();
            $requestData['use_local_shipping_rate_table'] = $listingProduct->getListingTemplate()->isUseEbayLocalShippingRateTableEnabled();
        }
        //-------------------
        
        // Get Refund Info
        //-------------------
        if ($permissions['general']) {
            $requestData['return_policy'] = $listingProduct->getListingTemplate()->getRefundOptions();
        }
        //-------------------

        // Get Images Info
        //-------------------
        $permissions['general'] && $this->addImagesData($listingProduct,$requestData);
        //-------------------

        return $requestData;
    }

    public function updateAfterListAction(Ess_M2ePro_Model_ListingsProducts $listingProduct, array $params = array())
    {
        // Add New eBay Item Id
        //---------------------
        $ebayItemsId = $this->createNewEbayItemsId($listingProduct,$params['ebay_item_id']);
        //---------------------

        // Save additional info
        //---------------------
        $additionalData = $listingProduct->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        !is_array($additionalData) && $additionalData = array();
        $additionalData['is_eps_ebay_images_mode'] = $params['is_eps_ebay_images_mode'];
        $listingProduct->addData(array('additional_data'=>json_encode($additionalData)))->save();
        //---------------------

        // Update Listing Product
        //---------------------
        $this->updateProductAfterAction($listingProduct,
                                        $ebayItemsId,
                                        $params['ebay_start_date_raw'],
                                        $params['ebay_end_date_raw'],
                                        $params['status_changer'],
                                        false);
        //---------------------

        // Update Variations
        //---------------------
        Mage::getModel('M2ePro/Connectors_Ebay_Item_HelperVariations')
                   ->updateAfterAction($listingProduct,false);
        //---------------------
    }

    //----------------------------------------

    public function getRelistRequestData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array $params = array())
    {
        $requestData = array();

        // Get eBay Item Info
        //-------------------
        $requestData['item_id'] = $listingProduct->getEbayItem()->getItemId();
        //-------------------
        
        // Prepare Variations
        //-------------------
        Mage::helper('M2ePro/Variations')->updateVariations($listingProduct);
        $tempVariations = Mage::getModel('M2ePro/Connectors_Ebay_Item_HelperVariations')
                                                ->getRequestData($listingProduct);

        $requestData['is_variation_item'] = false;
        if (is_array($tempVariations) && count($tempVariations) > 0) {
            $requestData['is_variation_item'] = true;
        }
        //-------------------

        // Add ebay image upload mode
        //---------------------
        $additionalData = $listingProduct->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        !is_array($additionalData) && $additionalData = array();
        if (isset($additionalData['is_eps_ebay_images_mode'])) {
            $requestData['is_eps_ebay_images_mode'] = $additionalData['is_eps_ebay_images_mode'];
        }
        //---------------------

        if (!$listingProduct->getSynchronizationTemplate()->isRelistSendData()) {
            return $requestData;
        }
        
        // Get Variations
        //-------------------
        if ($requestData['is_variation_item']) {

            $requestData['variation'] = $tempVariations;
            $requestData['variation_image'] = Mage::getModel('M2ePro/Connectors_Ebay_Item_HelperVariations')
                                                    ->getImagesData($listingProduct);
            if (count($requestData['variation_image']) == 0) {
                unset($requestData['variation_image']);
            }
        }
        //-------------------

        // Get General Info
        //-------------------
        $this->addDescriptionData($listingProduct,$requestData,array());

        if (!$requestData['is_variation_item']) {
            $this->addQtyPriceData($listingProduct,$requestData,array());
        }
        //-------------------

        return $requestData;
    }

    public function updateAfterRelistAction(Ess_M2ePro_Model_ListingsProducts $listingProduct, array $params = array())
    {
        // Add New eBay Item Id
        //---------------------
        $ebayItemsId = $this->createNewEbayItemsId($listingProduct,$params['ebay_item_id']);
        //---------------------

        // Update Listing Product
        //---------------------
        $this->updateProductAfterAction($listingProduct,
                                        $ebayItemsId,
                                        $params['ebay_start_date_raw'],
                                        $params['ebay_end_date_raw'],
                                        $params['status_changer'],
                                        false);
        //---------------------

        // Update Variations
        //---------------------
        Mage::getModel('M2ePro/Connectors_Ebay_Item_HelperVariations')
                   ->updateAfterAction($listingProduct,false);
        //---------------------
    }

    //----------------------------------------

    public function getReviseRequestData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array $params = array())
    {
        $requestData = $this->getListRequestData($listingProduct,$params);

        // Get eBay Item Info
        //-------------------
        $requestData['item_id'] = $listingProduct->getEbayItem()->getItemId();
        //-------------------
        
        // Delete purchased variations
        //-------------------
        if (isset($requestData['variation']) && count($requestData['variation']) > 0) {

            $newVariations = array();
            
            foreach ($requestData['variation'] as $variation) {

                if ((int)$variation['qty'] > 0) {
                    $newVariations[] = $variation;
                    continue;
                }

				ksort($variation['specifics']);
				$variationKeys = array_keys($variation['specifics']);
				$variationValues = array_values($variation['specifics']);

                $tempOrdersItemsCollection = Mage::getModel('M2ePro/Orders_OrderItem')->getCollection();
                $tempOrdersItemsCollection->addFieldToFilter('item_id', $requestData['item_id']);
                $ordersItems = $tempOrdersItemsCollection->toArray();

                $findOrderItem = false;

                foreach ($ordersItems['items'] as $orderItem) {

                    if (is_null($orderItem['variations'])) {
                        continue;
                    }

                    $orderItem['variations'] = unserialize($orderItem['variations']);

					ksort($orderItem['variations']);
					$orderItemVariationKeys = array_keys($orderItem['variations']);
					$orderItemVariationValues = array_values($orderItem['variations']);

					if (count($variation['specifics']) == count($orderItem['variations']) &&
						count(array_diff($variationKeys,$orderItemVariationKeys)) <= 0 &&
						count(array_diff($variationValues,$orderItemVariationValues)) <= 0) {
						$findOrderItem = true;
						break;
					}
                }

				if ($findOrderItem) {
                    $variation['ignored'] = true;
				}

                $newVariations[] = $variation;
            }

            $requestData['variation'] = $newVariations;
        }
        //-------------------

        // Add ebay image upload mode
        //---------------------
        $additionalData = $listingProduct->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        !is_array($additionalData) && $additionalData = array();
        if (isset($additionalData['is_eps_ebay_images_mode'])) {
            $requestData['is_eps_ebay_images_mode'] = $additionalData['is_eps_ebay_images_mode'];
        }
        //---------------------

        return $requestData;
    }

    public function updateAfterReviseAction(Ess_M2ePro_Model_ListingsProducts $listingProduct, array $params = array())
    {
        // Update Listing Product
        //---------------------
        $this->updateProductAfterAction($listingProduct,
                                        $listingProduct->getEbayItem()->getId(),
                                        $params['ebay_start_date_raw'],
                                        $params['ebay_end_date_raw'],
                                        $params['status_changer'],
                                        true);
        //---------------------

        // Update Variations
        //---------------------
        Mage::getModel('M2ePro/Connectors_Ebay_Item_HelperVariations')
                   ->updateAfterAction($listingProduct,true);
        //---------------------
    }

    //----------------------------------------

    public function getStopRequestData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array $params = array())
    {
        $requestData = array();
        
        // Get eBay Item Info
        //-------------------
        $requestData['item_id'] = $listingProduct->getEbayItem()->getItemId();
        //-------------------

        return $requestData;
    }

    public function updateAfterStopAction(Ess_M2ePro_Model_ListingsProducts $listingProduct, array $params = array())
    {
        // Update Listing Product
        //---------------------
        $dataForUpdate = array(
            'status' => Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED,
            'status_changer' => $params['status_changer']
        );
        if (isset($params['ebay_end_date_raw'])) {
            $dataForUpdate['ebay_end_date'] = Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($params['ebay_end_date_raw']);
        }
        $listingProduct->addData($dataForUpdate)->save();
        //---------------------

        // Update Variations
        //---------------------
        $productVariations = $listingProduct->getListingsProductsVariations(true);
        foreach ($productVariations as $variation) {
            /** @var $variation Ess_M2ePro_Model_ListingsProductsVariations */
            $dataForUpdate = array(
                'add' => Ess_M2ePro_Model_ListingsProductsVariations::ADD_NO
            );
            if ($variation->isListed()) {
                $dataForUpdate['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED;
            }
            $variation->addData($dataForUpdate)->save();
        }
        //---------------------
    }

    // ########################################

    protected function addSellingFormatData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array &$requestData)
    {
        if ($listingProduct->isListingTypeFixed()) {
            $requestData['listing_type'] = Ess_M2ePro_Model_SellingFormatTemplates::EBAY_LISTING_TYPE_FIXED;
        } else {
            $requestData['listing_type'] = Ess_M2ePro_Model_SellingFormatTemplates::EBAY_LISTING_TYPE_AUCTION;
        }

        $requestData['duration'] = $listingProduct->getDuration();
        $requestData['is_private'] = $listingProduct->getSellingFormatTemplate()->isPrivateListing();

        $requestData['currency'] = $listingProduct->getSellingFormatTemplate()->getCurrency();
        $requestData['hit_counter'] = $listingProduct->getDescriptionTemplate()->getHitCounterType();
    }
    
    protected function addDescriptionData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array &$requestData, $permissions = array())
    {
        if (!isset($permissions['title']) || $permissions['title']) {
            $requestData['title'] = $listingProduct->getTitle();
        }

        if (!isset($permissions['subtitle']) || $permissions['subtitle']) {
            $requestData['subtitle'] = $listingProduct->getSubTitle();
        }

        if (!isset($permissions['description']) || $permissions['description']) {
            $requestData['description'] = $listingProduct->getDescription();
        }
    }

    protected function addQtyPriceData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array &$requestData, $permissions = array())
    {
        if (!isset($permissions['qty']) || $permissions['qty']) {
            $requestData['qty'] = $listingProduct->getQty();
        }

        if (!isset($permissions['price']) || $permissions['price']) {
            
            if ($listingProduct->isListingTypeFixed()) {
                $requestData['price_fixed'] = $listingProduct->getBuyItNowPrice();
            } else {
                $requestData['price_start'] = $listingProduct->getStartPrice();
                $requestData['price_reserve'] = $listingProduct->getReservePrice();
                $requestData['price_buyitnow'] = $listingProduct->getBuyItNowPrice();
            }
        }
    }

    //----------------------------------------

    protected function addCategoriesData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array &$requestData)
    {
        $requestData['category_main_id'] = $listingProduct->getMainCategory();
        $requestData['category_secondary_id'] = $listingProduct->getSecondaryCategory();
    }

    protected function addStoreCategoriesData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array &$requestData)
    {
        $requestData['store_category_main_id'] = $listingProduct->getMainStoreCategory();
        $requestData['store_category_secondary_id'] = $listingProduct->getSecondaryStoreCategory();
    }

    //----------------------------------------
    
    protected function addBestOfferData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array &$requestData)
    {
        if ($listingProduct->isListingTypeFixed()) {
            $requestData['bestoffer_mode'] = $listingProduct->getSellingFormatTemplate()->isBestOfferEnabled();
            if ($requestData['bestoffer_mode']) {
                $requestData['bestoffer_accept_price'] = $listingProduct->getBestOfferAcceptPrice();
                $requestData['bestoffer_reject_price'] = $listingProduct->getBestOfferRejectPrice();
            }
        }
    }

    protected function addProductDetailsData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array &$requestData)
    {
        $requestData['product_details'] = array();

        $temp = $listingProduct->getProductDetail('isbn');
        $temp && $requestData['product_details']['isbn'] = $temp;

        $temp = $listingProduct->getProductDetail('epid');
        $temp && $requestData['product_details']['epid'] = $temp;

        $temp = $listingProduct->getProductDetail('upc');
        $temp && $requestData['product_details']['upc'] = $temp;

        $temp = $listingProduct->getProductDetail('ean');
        $temp && $requestData['product_details']['ean'] = $temp;
    }

    //----------------------------------------
    
    protected function addItemSpecificsData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array &$requestData)
    {
        $requestData['item_specifics'] = array();

        $tempListingSpecifics = $listingProduct->getListingTemplate()->getListingsTemplatesSpecifics(true);
        foreach ($tempListingSpecifics as $tempSpecific) {

            $tempSpecific->setMagentoProduct($listingProduct->getMagentoProduct());

            $tempAttributeData = $tempSpecific->getAttributeData();
            $tempAttributeValues = $tempSpecific->getValues();

            if (!$tempSpecific->isItemSpecificsMode()) {
                continue;
            }

            $values = array();
            foreach ($tempAttributeValues as $tempAttributeValue) {
                if ($tempAttributeValue['value'] == '--') {
                    continue;
                }
                $values[] = $tempAttributeValue['value'];
            }

            $requestData['item_specifics'][] = array(
                'name' => $tempAttributeData['id'],
                'value' => $values
            );
        }
    }

    protected function addAttributeSetData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array &$requestData)
    {
        $requestData['attribute_set'] = array(
            'attribute_set_id' => 0,
            'attributes' => array()
        );

        $tempListingSpecifics = $listingProduct->getListingTemplate()->getListingsTemplatesSpecifics(true);
        foreach ($tempListingSpecifics as $tempSpecific) {

            $tempSpecific->setMagentoProduct($listingProduct->getMagentoProduct());

            $tempAttributeData = $tempSpecific->getAttributeData();
            $tempAttributeValues = $tempSpecific->getValues();

            if (!$tempSpecific->isAttributeSetMode()) {
                continue;
            }

            $requestData['attribute_set']['attribute_set_id'] = $tempSpecific->getModeRelationId();
            $requestData['attribute_set']['attributes'][] = array(
                'id' => $tempAttributeData['id'],
                'value' => $tempAttributeValues
            );
        }
    }

    //----------------------------------------

    protected function addShippingData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array &$requestData)
    {
        $requestData['shipping'] = array();

        if ($listingProduct->getListingTemplate()->isLocalShippingEnabled()) {

            $requestData['shipping']['local'] = array();

            if ($listingProduct->getListingTemplate()->isLocalShippingFreightEnabled()) {
                $requestData['shipping']['local']['type'] = Ess_M2ePro_Model_ListingsTemplates::EBAY_SHIPPING_TYPE_FREIGHT;
            }
            if ($listingProduct->getListingTemplate()->isLocalShippingLocalEnabled()) {
                $requestData['shipping']['local']['type'] = Ess_M2ePro_Model_ListingsTemplates::EBAY_SHIPPING_TYPE_LOCAL;
            }
            if ($listingProduct->getListingTemplate()->isLocalShippingFlatEnabled()) {
                $requestData['shipping']['local']['type'] = Ess_M2ePro_Model_ListingsTemplates::EBAY_SHIPPING_TYPE_FLAT;
            }

            if ($listingProduct->getListingTemplate()->isLocalShippingCalculatedEnabled()) {

                $requestData['shipping']['local']['type'] = Ess_M2ePro_Model_ListingsTemplates::EBAY_SHIPPING_TYPE_CALCULATED;
                $requestData['shipping']['local']['handing_fee'] = $listingProduct->getLocalHandling();

                $requestData['shipping']['calculated'] = array(
                    'measurement_system' => $listingProduct->getListingTemplate()->getCalculatedShipping()->getMeasurementSystem(),
                    'package_size' => $listingProduct->getPackageSize(),
                    'originating_postal_code' => $listingProduct->getListingTemplate()->getCalculatedShipping()->getPostalCode(),
                    'dimensions' => $listingProduct->getDimensions(),
                    'weight' => $listingProduct->getWeight()
                );
                if ($requestData['shipping']['calculated']['measurement_system'] == Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::MEASUREMENT_SYSTEM_ENGLISH) {
                    $requestData['shipping']['calculated']['measurement_system'] = Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::EBAY_MEASUREMENT_SYSTEM_ENGLISH;
                }
                if ($requestData['shipping']['calculated']['measurement_system'] == Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::MEASUREMENT_SYSTEM_METRIC) {
                    $requestData['shipping']['calculated']['measurement_system'] = Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::EBAY_MEASUREMENT_SYSTEM_METRIC;
                }
            }

            if ($listingProduct->getListingTemplate()->isLocalShippingFlatEnabled() || $listingProduct->getListingTemplate()->isLocalShippingCalculatedEnabled()) {

                $requestData['shipping']['get_it_fast'] = $listingProduct->getListingTemplate()->isGetItFastEnabled();
                $requestData['shipping']['dispatch_time'] = $listingProduct->getListingTemplate()->getDispatchTime();
                $requestData['shipping']['local']['discount'] = $listingProduct->getListingTemplate()->isLocalShippingDiscountEnabled();
                $requestData['shipping']['local']['methods'] = array();

                $tempShippingsMethods = $listingProduct->getListingTemplate()->getListingsTemplatesShippings(true);
                foreach ($tempShippingsMethods as $tempMethod) {
                    if (!$tempMethod->isShippingTypeLocal()) {
                       continue;
                    }
                    $tempMethod->setMagentoProduct($listingProduct->getMagentoProduct());
                    $tempDataMethod = array(
                        'service' => $tempMethod->getShippingValue()
                    );
                    if ($listingProduct->getListingTemplate()->isLocalShippingFlatEnabled()) {
                        $tempDataMethod['cost'] = $tempMethod->getCost();
                        $tempDataMethod['cost_additional'] = $tempMethod->getCostAdditional();
                    }
                    if ($listingProduct->getListingTemplate()->isLocalShippingCalculatedEnabled()) {
                        $tempDataMethod['is_free'] = $tempMethod->isCostModeFree();
                    }
                    $requestData['shipping']['local']['methods'][] = $tempDataMethod;
                }
            }
        }

        if ($listingProduct->getListingTemplate()->isInternationalShippingEnabled() &&
            !$listingProduct->getListingTemplate()->isLocalShippingFreightEnabled() &&
            !$listingProduct->getListingTemplate()->isLocalShippingLocalEnabled()) {
            
            $requestData['shipping']['international'] = array();

            if ($listingProduct->getListingTemplate()->isInternationalShippingFlatEnabled()) {
                $requestData['shipping']['international']['type'] = Ess_M2ePro_Model_ListingsTemplates::EBAY_SHIPPING_TYPE_FLAT;
            }

            if ($listingProduct->getListingTemplate()->isInternationalShippingCalculatedEnabled()) {
                $requestData['shipping']['international']['type'] = Ess_M2ePro_Model_ListingsTemplates::EBAY_SHIPPING_TYPE_CALCULATED;
                $requestData['shipping']['international']['handing_fee'] = $listingProduct->getInternationalHandling();
                if (!isset($requestData['shipping']['calculated'])) {
                    $requestData['shipping']['calculated'] = array(
                        'measurement_system' => $listingProduct->getListingTemplate()->getCalculatedShipping()->getMeasurementSystem(),
                        'package_size' => $listingProduct->getPackageSize(),
                        'originating_postal_code' => $listingProduct->getListingTemplate()->getCalculatedShipping()->getPostalCode(),
                        'dimensions' => $listingProduct->getDimensions(),
                        'weight' => $listingProduct->getWeight()
                    );
                    if ($requestData['shipping']['calculated']['measurement_system'] == Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::MEASUREMENT_SYSTEM_ENGLISH) {
                        $requestData['shipping']['calculated']['measurement_system'] = Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::EBAY_MEASUREMENT_SYSTEM_ENGLISH;
                    }
                    if ($requestData['shipping']['calculated']['measurement_system'] == Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::MEASUREMENT_SYSTEM_METRIC) {
                        $requestData['shipping']['calculated']['measurement_system'] = Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::EBAY_MEASUREMENT_SYSTEM_METRIC;
                    }
                }
            }

            $requestData['shipping']['international']['discount'] = $listingProduct->getListingTemplate()->isInternationalShippingDiscountEnabled();
            $requestData['shipping']['international']['methods'] = array();

            $tempShippingsMethods = $listingProduct->getListingTemplate()->getListingsTemplatesShippings(true);
            foreach ($tempShippingsMethods as $tempMethod) {
                if (!$tempMethod->isShippingTypeInternational()) {
                   continue;
                }
                $tempMethod->setMagentoProduct($listingProduct->getMagentoProduct());
                $tempDataMethod = array(
                    'service' => $tempMethod->getShippingValue(),
                    'locations' => $tempMethod->getLocations()
                );
                if ($listingProduct->getListingTemplate()->isInternationalShippingFlatEnabled()) {
                    $tempDataMethod['cost'] = $tempMethod->getCost();
                    $tempDataMethod['cost_additional'] = $tempMethod->getCostAdditional();
                }
                $requestData['shipping']['international']['methods'][] = $tempDataMethod;
            }
        }
    }

    //----------------------------------------
    
    protected function addPaymentData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array &$requestData)
    {
        $requestData['payment'] = array(
            'methods' => $listingProduct->getListingTemplate()->getPaymentMethods()
        );

        if (in_array('PayPal',$requestData['payment']['methods'])) {
            $requestData['payment']['paypal'] = array(
                'email' => $listingProduct->getListingTemplate()->getPayPalEmailAddress(),
                'immediate_payment' => $listingProduct->getListingTemplate()->isPayPalImmediatePaymentEnabled()
            );
        }
    }

    //----------------------------------------

    protected function addImagesData(Ess_M2ePro_Model_ListingsProducts $listingProduct, array &$requestData)
    {
        $requestData['images'] = array(
            'gallery_type' => $listingProduct->getListingTemplate()->getGalleryType(),
            'images' => $listingProduct->getImagesForEbay()
        );
    }

    // ########################################

    protected function createNewEbayItemsId(Ess_M2ePro_Model_ListingsProducts $listingProduct, $ebayRealItemId)
    {
        $dataForAdd = array(
            'item_id' => (double)$ebayRealItemId,
            'product_id' => (int)$listingProduct->getProductId(),
            'store_id' => (int)$listingProduct->getListing()->getStoreId()
        );
        return Mage::getModel('M2ePro/EbayItems')->setData($dataForAdd)->save()->getId();
    }

    protected function updateProductAfterAction(Ess_M2ePro_Model_ListingsProducts $listingProduct,
                                                $ebayItemsId, $ebayStartDateRaw, $ebayEndDateRaw,
                                                $statusChanger, $saveEbayQtySold = false)
    {
        $dataForUpdate = array(
            'ebay_items_id' => (int)$ebayItemsId,
            'ebay_start_date' => Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($ebayStartDateRaw),
            'ebay_end_date' => Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($ebayEndDateRaw),
            'status' => Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED,
            'status_changer' => $statusChanger
        );

        if ($saveEbayQtySold) {
            $dataForUpdate['ebay_qty_sold'] = is_null($listingProduct->getEbayQtySold()) ? 0 : $listingProduct->getEbayQtySold();
            $dataForUpdate['ebay_qty'] = $listingProduct->getQty() + $dataForUpdate['ebay_qty_sold'];
        } else {
            $dataForUpdate['ebay_qty_sold'] = 0;
            $dataForUpdate['ebay_qty'] = $listingProduct->getQty();
        }

        if ($listingProduct->isListingTypeFixed()) {
            $dataForUpdate['ebay_start_price'] = NULL;
            $dataForUpdate['ebay_reserve_price'] = NULL;
            $dataForUpdate['ebay_buyitnow_price'] = $listingProduct->getBuyItNowPrice();
            $dataForUpdate['ebay_bids'] = NULL;
        } else {
            $dataForUpdate['ebay_start_price'] = $listingProduct->getStartPrice();
            $dataForUpdate['ebay_reserve_price'] = $listingProduct->getReservePrice();
            $dataForUpdate['ebay_buyitnow_price'] = $listingProduct->getBuyItNowPrice();
            $dataForUpdate['ebay_bids'] = 0;
        }

        $listingProduct->addData($dataForUpdate)->save();
    }

    // ########################################
}