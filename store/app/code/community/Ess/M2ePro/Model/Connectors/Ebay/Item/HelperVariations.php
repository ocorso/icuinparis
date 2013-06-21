<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_Item_HelperVariations extends Mage_Core_Model_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Connectors_Ebay_Item_HelperVariations');
    }

    // ########################################

    public function getRequestData(Ess_M2ePro_Model_ListingsProducts $listingProduct)
    {
        if (!$listingProduct->isListingTypeFixed() ||
            !$listingProduct->getListingTemplate()->isVariationMode() ||
            $listingProduct->getMagentoProduct()->isSimpleTypeWithoutCustomOptions()) {
            return array();
        }

        $requestData = array();

        // Get Request Variations Data
        //-----------------------------
        $productVariations = $listingProduct->getListingsProductsVariations(true);
        foreach ($productVariations as $variation) {

            /** @var $variation Ess_M2ePro_Model_ListingsProductsVariations */
            $tempItem = array(
                'price' => $variation->getPrice(),
                'qty' => $variation->isDelete() ? 0 : $variation->getQty(),
                'sku' => $variation->getSku(),
                'specifics' => array()
            );

            $productVariationsOptions = $variation->getListingsProductsVariationsOptions(true);
            foreach ($productVariationsOptions as $option) {
                /** @var $option Ess_M2ePro_Model_ListingsProductsVariationsOptions */
                $tempItem['specifics'][$option->getAttribute()] = $option->getOption();
            }

            $requestData[] = $tempItem;
        }
        //-----------------------------

        return $requestData;
    }

    public function getImagesData(Ess_M2ePro_Model_ListingsProducts $listingProduct)
    {
        if (!$listingProduct->isListingTypeFixed() ||
            !$listingProduct->getListingTemplate()->isVariationMode() ||
            $listingProduct->getMagentoProduct()->isSimpleTypeWithoutCustomOptions()) {
            return array();
        }

        $tempSpecifics = array();
        
        if ($listingProduct->getMagentoProduct()->isConfigurableType() &&
            $listingProduct->getDescriptionTemplate()->isVariationConfigurableImages()) {

            $attributeCode = $listingProduct->getDescriptionTemplate()->getVariationConfigurableImages();
            $attributeData = $listingProduct->getMagentoProduct()->getProduct()->getResource()
                                                        ->getAttribute($attributeCode)->getData();

            $tempProduct = $listingProduct->getMagentoProduct()->getProduct();
            $configurableAttributes = $tempProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($tempProduct);

            foreach ($configurableAttributes as $configurableAttribute) {
                if ((int)$attributeData['attribute_id'] == (int)$configurableAttribute['attribute_id']) {
                    $tempSpecifics = array(
                        $configurableAttribute['label'],
                        $configurableAttribute['frontend_label'],
                        $configurableAttribute['store_label']
                    );
                    break;
                }
            }
        }

        if ($listingProduct->getMagentoProduct()->isGroupedType()) {
            $tempSpecifics = array(Ess_M2ePro_Model_MagentoProduct::GROUPED_PRODUCT_ATTRIBUTE_LABEL);
        }

        $requestData = array(
            'specific' => '',
            'images' => array()
        );
        
        if (count($tempSpecifics) > 0) {

            $productVariations = $listingProduct->getListingsProductsVariations(true);
            foreach ($productVariations as $variation) {

                /** @var $variation Ess_M2ePro_Model_ListingsProductsVariations */

                if ($variation->isDelete()) {
                    continue;
                }

                $productVariationsOptions = $variation->getListingsProductsVariationsOptions(true);

                foreach ($productVariationsOptions as $option) {

                    /** @var $option Ess_M2ePro_Model_ListingsProductsVariationsOptions */

                    $findedSpecific = false;
                    foreach ($tempSpecifics as $tempSpecific) {
                        if ($tempSpecific == $option->getAttribute()) {
                            $findedSpecific = $tempSpecific;
                        }
                    }

                    if ($findedSpecific === false) {
                        continue;
                    }

                    $requestData['specific'] = $findedSpecific;
                    
                    $images = $option->getImagesForEbay();
                    if (count($images) > 0) {
                        $requestData['images'][$option->getOption()] = array_slice($images,0,1);
                        //!isset($requestData['images'][$option->getOption()]) && $requestData['images'][$option->getOption()] = array();
                        //$requestData['images'][$option->getOption()] = array_merge($requestData['images'][$option->getOption()],$images);
                        //$requestData['images'][$option->getOption()] = array_unique($requestData['images'][$option->getOption()]);
                        //$requestData['images'][$option->getOption()] = array_slice($requestData['images'][$option->getOption()],0,12);
                    }
                }
            }
        }

        if ($requestData['specific'] == '' || count($requestData['images']) <= 0) {
            return array();
        }
        
        return $requestData;
    }

    // ########################################

    public function updateAfterAction(Ess_M2ePro_Model_ListingsProducts $listingProduct, $saveEbayQtySold = false)
    {
        if (!$listingProduct->isListingTypeFixed() ||
            !$listingProduct->getListingTemplate()->isVariationMode() ||
            $listingProduct->getMagentoProduct()->isSimpleTypeWithoutCustomOptions()) {
            return;
        }

        // Delete Variations
        //-----------------------------
        $productVariations = $listingProduct->getListingsProductsVariations(true);
        foreach ($productVariations as $variation) {
            /** @var $variation Ess_M2ePro_Model_ListingsProductsVariations */
            $variation->isDelete() && $variation->deleteInstance();
        }
        //-----------------------------

        // Update Variations
        //-----------------------------
        $productVariations = $listingProduct->getListingsProductsVariations(true);
        foreach ($productVariations as $variation) {
            /** @var $variation Ess_M2ePro_Model_ListingsProductsVariations */
            $dataForUpdate = array(
                'ebay_price' => $variation->getPrice(),
                'add' => Ess_M2ePro_Model_ListingsProductsVariations::ADD_NO,
                'delete' => Ess_M2ePro_Model_ListingsProductsVariations::DELETE_NO,
                'status' => Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED
            );

            if ($saveEbayQtySold) {
                $dataForUpdate['ebay_qty_sold'] = is_null($variation->getEbayQtySold()) ? 0 : $variation->getEbayQtySold();
                $dataForUpdate['ebay_qty'] = $variation->getQty()  + $dataForUpdate['ebay_qty_sold'];
            } else {
                $dataForUpdate['ebay_qty_sold'] = 0;
                $dataForUpdate['ebay_qty'] = $variation->getQty();
            }

            if ($dataForUpdate['ebay_qty'] <= $dataForUpdate['ebay_qty_sold']) {
                $dataForUpdate['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_SOLD;
            }
            if ($dataForUpdate['ebay_qty'] <= 0) {
                $dataForUpdate['status'] = Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED;
            }
            
            $variation->addData($dataForUpdate)->save();
        }
        //-----------------------------
    }

    // ########################################
}