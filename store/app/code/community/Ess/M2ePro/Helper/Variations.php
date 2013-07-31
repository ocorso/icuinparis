<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Variations extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function updateVariations(Ess_M2ePro_Model_ListingsProducts $listingProduct)
    {
        if (!$listingProduct->isListingTypeFixed() ||
            !$listingProduct->getListingTemplate()->isVariationMode() ||
            $listingProduct->getMagentoProduct()->isSimpleTypeWithoutCustomOptions()) {
            return;
        }

        // Get Variations
        //-----------------------------
        $magentoVariations = $this->prepareMagentoVariations($listingProduct->getMagentoProduct()->getProductVariations());
        $currentVariations = $this->prepareCurrentVariations($listingProduct->getListingsProductsVariations(true));
        //-----------------------------

        // Get Variations Changes
        //-----------------------------
        $addedVariations = $this->getAddedVariations($magentoVariations,$currentVariations);
        $deletedVariations = $this->getDeletedVariations($magentoVariations,$currentVariations);
        //-----------------------------

        // Add And Mark As Delete from DB
        //-----------------------------
        $this->addVariations($listingProduct,$addedVariations);
        $this->markAsDeleteVariations($deletedVariations);
        //-----------------------------
    }

    public function isAddedNewVariationsAttributes(Ess_M2ePro_Model_ListingsProducts $listingProduct)
    {
        if (!$listingProduct->isListingTypeFixed() ||
            !$listingProduct->getListingTemplate()->isVariationMode() ||
            $listingProduct->getMagentoProduct()->isSimpleTypeWithoutCustomOptions()) {
            return false;
        }

        $magentoVariations = $this->prepareMagentoVariations($listingProduct->getMagentoProduct()->getProductVariations());
        $currentVariations = $this->prepareCurrentVariations($listingProduct->getListingsProductsVariations(true));

        if (!isset($magentoVariations[0]) && !isset($currentVariations[0])) {
            return false;
        }

        if (!isset($magentoVariations[0]) || !isset($currentVariations[0])) {
            return true;
        }

        if (count($magentoVariations[0]['options']) != count($currentVariations[0]['options'])) {
            return true;
        }

        return false;
    }

    // ########################################

    protected function prepareMagentoVariations($variations)
    {
        $result = array();

        if (isset($variations['variation'])) {
            $variations = $variations['variation'];
        }

        foreach ($variations as $variation) {
            $variation['variation'] = array();
            if (isset($variation['specifics'])) {
                $variation['options'] = $variation['specifics'];
                unset($variation['specifics']);
            }
            $result[] = $variation;
        }

        return $result;
    }

    protected function prepareCurrentVariations($variations)
    {
        $result = array();

        foreach ($variations as $variation) {
            $temp = array(
                'variation' => $variation->getData(),
                'options' => array()
            );
            foreach ($variation->getListingsProductsVariationsOptions(false) as $option) {
                $temp['options'][] = $option;
            }
            $result[] = $temp;
        }

        return $result;
    }

    //----------------------------------------

    protected function getAddedVariations($magentoVariations, $currentVariations)
    {
        $result = array();

        foreach ($magentoVariations as $mVariation) {
            $isExistVariation = false;
            $cVariationExist = NULL;
            foreach ($currentVariations as $cVariation) {
                if ($this->isEqualVariations($mVariation['options'],$cVariation['options'])) {
                    $isExistVariation = true;
                    $cVariationExist = $cVariation;
                    break;
                }
            }
            if (!$isExistVariation) {
                $result[] = $mVariation;
            } else {
                if ((int)$cVariationExist['variation']['delete'] == Ess_M2ePro_Model_ListingsProductsVariations::DELETE_YES) {
                    $result[] = $cVariationExist;
                }
            }
        }

        return $result;
    }

    protected function getDeletedVariations($magentoVariations, $currentVariations)
    {
        $result = array();

        foreach ($currentVariations as $cVariation) {
            if ((int)$cVariation['variation']['delete'] == Ess_M2ePro_Model_ListingsProductsVariations::DELETE_YES) {
                continue;
            }
            $isExistVariation = false;
            foreach ($magentoVariations as $mVariation) {
                if ($this->isEqualVariations($mVariation['options'],$cVariation['options'])) {
                    $isExistVariation = true;
                    break;
                }
            }
            if (!$isExistVariation) {
                $result[] = $cVariation;
            }
        }

        return $result;
    }

    //----------------------------------------

    protected function isEqualVariations($magentoVariation, $currentVariation)
    {
        if (count($magentoVariation) != count($currentVariation)) {
            return false;
        }

        foreach ($magentoVariation as $mOption) {
            $haveOption = false;
            foreach ($currentVariation as $cOption) {
                if ($mOption['attribute'] == $cOption['attribute'] &&
                    $mOption['option'] == $cOption['option']) {
                    $haveOption = true;
                    break;
                }
            }
            if (!$haveOption) {
                return false;
            }
        }

        return true;
    }

    //----------------------------------------

    protected function addVariations(Ess_M2ePro_Model_ListingsProducts $listingProduct, $addedVariations)
    {
        foreach ($addedVariations as $aVariation) {

            if (isset($aVariation['variation']['id'])) {
                $dataForUpdate = array(
                    'add' => Ess_M2ePro_Model_ListingsProductsVariations::ADD_YES,
                    'delete' => Ess_M2ePro_Model_ListingsProductsVariations::DELETE_NO
                );
                Mage::getModel('M2ePro/ListingsProductsVariations')
                        ->loadInstance($aVariation['variation']['id'])
                        ->addData($dataForUpdate)
                        ->save();
                continue;
            }

            $dataForAdd = array(
                'listing_product_id' => $listingProduct->getId(),
                'add' => Ess_M2ePro_Model_ListingsProductsVariations::ADD_YES,
                'delete' => Ess_M2ePro_Model_ListingsProductsVariations::DELETE_NO,
                'status' => Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED
            );
            $newVariationId = Mage::getModel('M2ePro/ListingsProductsVariations')
                                        ->addData($dataForAdd)
                                        ->save()
                                        ->getId();

            foreach ($aVariation['options'] as $aOption) {
                $dataForAdd = array(
                    'listing_product_variation_id' => $newVariationId,
                    'product_id' => $aOption['product_id'],
                    'product_type' => $aOption['product_type'],
                    'attribute' => $aOption['attribute'],
                    'option' => $aOption['option']
                );
                Mage::getModel('M2ePro/ListingsProductsVariationsOptions')
                        ->addData($dataForAdd)
                        ->save();
            }
        }
    }

    protected function markAsDeleteVariations($deletedVariations)
    {
        foreach ($deletedVariations as $dVariation) {
            if ($dVariation['variation']['status'] == Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED) {
                Mage::getModel('M2ePro/ListingsProductsVariations')
                            ->loadInstance($dVariation['variation']['id'])
                            ->deleteInstance();
            } else {
                $dataForUpdate = array(
                    'add' => Ess_M2ePro_Model_ListingsProductsVariations::ADD_NO,
                    'delete' => Ess_M2ePro_Model_ListingsProductsVariations::DELETE_YES
                );
                Mage::getModel('M2ePro/ListingsProductsVariations')
                        ->loadInstance($dVariation['variation']['id'])
                        ->addData($dataForUpdate)
                        ->save();
            }
        }
    }

    // ########################################
}