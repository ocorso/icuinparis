<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Category
{
    //####################################

    public function catalogCategoryChangeProducts(Varien_Event_Observer $observer)
    {
        try {

            // Get category data
            //---------------------------
            $categoryId = $observer->getData('category')->getId();
            $storeId = $observer->getData('category')->getData('store_id');
            //---------------------------

            // Get changes into categories
            //---------------------------
            $changedProductsIds = $observer->getData('product_ids');

            if (count($changedProductsIds) == 0) {
                return;
            }

            $tempArray = $observer->getData('category')->getData('posted_products');
            $postedProductsIds = array();
            foreach ($tempArray as $key => $value) {
                $postedProductsIds[] = $key;
            }

            $addedProducts = array();
            $deletedProducts = array();

            foreach ($changedProductsIds as $productId) {

                if (in_array($productId,$postedProductsIds)) {
                    $addedProducts[] = $productId;
                } else {
                    $deletedProducts[] = $productId;
                }
            }

            if (count($addedProducts) == 0 && count($deletedProducts) == 0) {
                return;
            }
            //---------------------------

            // Make changes with listings
            self::synchChangesWithListings($categoryId,$storeId,$addedProducts,$deletedProducts);

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}
            
            return;
        }
    }

    //-----------------------------------

    public static function synchChangesWithListings($categoryId,
                                                    $storeId,
                                                    $addedProducts,
                                                    $deletedProducts)
    {
        try {
            
            // Check listings categories
            //---------------------------
            $listingsCategories = Mage::getModel('M2ePro/ListingsCategories')
                                            ->getCollection()
                                            ->addFieldToFilter('category_id', $categoryId)
                                            ->toArray();
            if ($listingsCategories['totalRecords'] == 0) {
                return;
            }
            //---------------------------

            // Get all related listings
            //---------------------------
            $listingsIds = array();
            foreach ($listingsCategories['items'] as $listingCategory) {
                $listingsIds[] = $listingCategory['listing_id'];
            }
            $listingsIds = array_unique($listingsIds);

            if (count($listingsIds) == 0) {
                return;
            }

            $listingsModels = array();
            foreach ($listingsIds as $listingId) {
                $tempModel = Mage::getModel('M2ePro/Listings')->loadInstance($listingId);
                /** @var $tempModel Ess_M2ePro_Model_Listings */
                if (!$tempModel->isSourceCategories() || $tempModel->getStoreId() != $storeId) {
                    continue;
                }
                $listingsModels[] = $tempModel;
            }

            if (count($listingsModels) == 0) {
                return;
            }
            //---------------------------

            // Add new products
            //---------------------------
            foreach ($addedProducts as $productId) {

                foreach ($listingsModels as $listingModel) {

                    /** @var $listingModel Ess_M2ePro_Model_Listings */

                    // Cancel when auto add none set
                    //------------------------------
                    if ($listingModel->getData('categories_add_action') == Ess_M2ePro_Model_Listings::CATEGORIES_ADD_ACTION_NONE) {
                        continue;
                    }
                    //------------------------------

                    // Only add product
                    //------------------------------
                    if ($listingModel->getData('categories_add_action') == Ess_M2ePro_Model_Listings::CATEGORIES_ADD_ACTION_ADD) {
                        $listingModel->addProduct($productId);
                    }
                    //------------------------------

                    // Add product and list on ebay
                    //------------------------------
                    if ($listingModel->getData('categories_add_action') == Ess_M2ePro_Model_Listings::CATEGORIES_ADD_ACTION_ADD_LIST) {

                        /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
                        $listingProduct = $listingModel->addProduct($productId);

                        if (!($listingProduct instanceof Ess_M2ePro_Model_ListingsProducts)) {
                            $tempListingsProducts = $listingModel->getListingsProducts(true,array('product_id'=>$productId));
                            count($tempListingsProducts) > 0 && $listingProduct = $tempListingsProducts[0];
                        }

                        if ($listingProduct instanceof Ess_M2ePro_Model_ListingsProducts) {
                            $paramsTemp = array();
                            $paramsTemp['status_changer'] = Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_OBSERVER;
                            $listingProduct->isListable() && $listingProduct->listEbay($paramsTemp);
                        }
                    }
                    //------------------------------
                }
            }
            //---------------------------

            // Delete products
            //---------------------------
            foreach ($deletedProducts as $productId) {

                foreach ($listingsModels as $listingModel) {

                    /** @var $listingModel Ess_M2ePro_Model_Listings */

                    // Cancel when auto delete none set
                    //------------------------------
                    if ($listingModel->getData('categories_delete_action') == Ess_M2ePro_Model_Listings::CATEGORIES_DELETE_ACTION_NONE) {
                        continue;
                    }
                    //------------------------------

                    // Find needed product
                    //------------------------------
                    $listingsProducts = $listingModel->getListingsProducts(true,array('product_id'=>$productId));

                    if (count($listingsProducts) <= 0) {
                        continue;
                    }

                    $listingProduct = $listingsProducts[0];
                    
                    if (!($listingProduct instanceof Ess_M2ePro_Model_ListingsProducts)) {
                        continue;
                    }
                    //------------------------------

                    // Only stop product
                    //------------------------------
                    if ($listingModel->getData('categories_delete_action') == Ess_M2ePro_Model_Listings::CATEGORIES_DELETE_ACTION_STOP) {
                        $paramsTemp = array();
                        $paramsTemp['status_changer'] = Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_OBSERVER;
                        $listingProduct->isStoppable() && $listingProduct->stopEbay($paramsTemp);
                    }
                    //------------------------------

                    // Stop product on ebay and remove
                    //------------------------------
                    if ($listingModel->getData('categories_delete_action') == Ess_M2ePro_Model_Listings::CATEGORIES_DELETE_ACTION_STOP_REMOVE) {
                        $paramsTemp = array();
                        $paramsTemp['status_changer'] = Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_OBSERVER;
                        $paramsTemp['remove'] = true;
                        $listingProduct->stopEbay($paramsTemp);
                    }
                    //------------------------------
                }
            }
            //---------------------------

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}

            return;
        }
    }

    //####################################
}