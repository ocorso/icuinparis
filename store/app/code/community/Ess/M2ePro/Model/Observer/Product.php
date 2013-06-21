<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Product
{
    private $_productNameOld = '';
    private $_productCategoriesOld = array();

    private $_productStatusOld = '';
    private $_productPriceOld = 0;
    private $_productSpecialPriceOld = 0;

    private $_productSpecialPriceFromDate = NULL;
    private $_productSpecialPriceToDate = NULL;

    private $_productCustomAttributes = array();

    //####################################

    public function catalogProductSaveBefore(Varien_Event_Observer $observer)
    {
        try {

            $productNew = $observer->getEvent()->getProduct();

            if (!($productNew instanceof Mage_Catalog_Model_Product)) {
                return;
            }

            $productOld = Mage::getModel('catalog/product')->load($productNew->getId());

            // Save preview name
            $this->_productNameOld = $productOld->getName();

            // Save preview categories
            $this->_productCategoriesOld = array_keys($productOld->getCategoryCollection()->exportToArray());

            // Get listing where is product
            $listingsIds = Mage::getResourceModel('M2ePro/Listings')->getListingsWhereIsProduct($productOld->getId());

            if (count($listingsIds) > 0) {

                // Save preview status
                $this->_productStatusOld = (int)$productOld->getStatus();

                // Save preview prices
                //--------------------
                $this->_productPriceOld = (float)$productOld->getPrice();
                $this->_productSpecialPriceOld = (float)$productOld->getSpecialPrice();

                $this->_productSpecialPriceFromDate = $productOld->getSpecialFromDate();
                $this->_productSpecialPriceToDate = $productOld->getSpecialToDate();
                //--------------------

                // Save preview attributes
                //--------------------
                $magentoProductModel = Mage::getModel('M2ePro/MagentoProduct')->setProduct($productOld);
                $this->_productCustomAttributes = $this->getCustomAttributes($listingsIds);
                foreach ($this->_productCustomAttributes as &$attribute) {
                    $attribute['value_old'] = $magentoProductModel->getAttributeValue($attribute['attribute']);
                }
                //--------------------
            }

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}

            return;
        }
    }

    public function catalogProductSaveAfter(Varien_Event_Observer $observer)
    {
        try {

            $productNew = $observer->getEvent()->getProduct();

            if (!($productNew instanceof Mage_Catalog_Model_Product)) {
                return;
            }

            // Update product name for listing log
            //--------------------
            $nameOld = $this->_productNameOld;
            $nameNew = $productNew->getName();

            if ($nameOld != $nameNew && $productNew->getStoreId() == 0) {
                Mage::getModel('M2ePro/ListingsLogs')->updateProductTitle($productNew->getId(),$nameNew);
            }
            //--------------------

            // Get listing where is product
            $listingsIds = Mage::getResourceModel('M2ePro/Listings')->getListingsWhereIsProduct($productNew->getId());

            if (count($listingsIds) > 0) {

                  // Save global changes
                  //--------------------
                  Mage::getModel('M2ePro/ProductsChanges')
                                    ->updateAttribute( $productNew->getId(),
                                                       'product_instance',
                                                       'any_old',
                                                       'any_new',
                                                        Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_OBSERVER );
                  //--------------------
                
                  // Save changes for status
                  //--------------------
                  $statusOld = (int)$this->_productStatusOld;
                  $statusNew = (int)$productNew->getStatus();

                  $rez = Mage::getModel('M2ePro/ProductsChanges')
                                ->updateAttribute(  $productNew->getId(),
                                                    'status',
                                                    $statusOld,
                                                    $statusNew,
                                                    Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_OBSERVER );

                  if ($rez !== false) {

                      $statusOld = ($statusOld == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)?'Enabled':'Disabled';
                      $statusNew = ($statusNew == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)?'Enabled':'Disabled';

                      foreach ($listingsIds as $listingId) {

                             Mage::getModel('M2ePro/ListingsLogs')
                                    ->addProductMessage( $listingId,
                                                         $productNew->getId(),
                                                         Ess_M2ePro_Model_ListingsLogs::INITIATOR_EXTENSION,
                                                         NULL,
                                                         Ess_M2ePro_Model_ListingsLogs::ACTION_CHANGE_PRODUCT_STATUS,
                                                         // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                                                         // Parser hack -> Mage::helper('M2ePro')->__('Enabled');
                                                         // Parser hack -> Mage::helper('M2ePro')->__('Disabled');
                                                         Mage::getModel('M2ePro/LogsBase')->encodeDescription('From [%from%] to [%to%]',array('from'=>$statusOld,'to'=>$statusNew)),
                                                         Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE,
                                                         Ess_M2ePro_Model_ListingsLogs::PRIORITY_LOW );
                      }
                  }
                  //--------------------

                  // Save changes for price
                  //--------------------
                  $priceOld = round((float)$this->_productPriceOld,2);
                  $priceNew = round((float)$productNew->getPrice(),2);

                  $rez = Mage::getModel('M2ePro/ProductsChanges')
                                ->updateAttribute(  $productNew->getId(),
                                                    'price',
                                                    $priceOld,
                                                    $priceNew,
                                                    Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_OBSERVER );

                  if ($rez !== false) {

                      foreach ($listingsIds as $listingId) {

                             Mage::getModel('M2ePro/ListingsLogs')
                                    ->addProductMessage( $listingId,
                                                         $productNew->getId(),
                                                         Ess_M2ePro_Model_ListingsLogs::INITIATOR_EXTENSION,
                                                         NULL,
                                                         Ess_M2ePro_Model_ListingsLogs::ACTION_CHANGE_PRODUCT_PRICE,
                                                         // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                                                         Mage::getModel('M2ePro/LogsBase')->encodeDescription('From [%from%] to [%to%]',array('!from'=>$priceOld,'!to'=>$priceNew)),
                                                         Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE,
                                                         Ess_M2ePro_Model_ListingsLogs::PRIORITY_LOW );
                      }
                  }
                  //--------------------

                  // Save changes for special price
                  //--------------------
                  $specialPriceOld = round((float)$this->_productSpecialPriceOld,2);
                  $specialPriceNew = round((float)$productNew->getSpecialPrice(),2);

                  $rez = Mage::getModel('M2ePro/ProductsChanges')
                                ->updateAttribute(  $productNew->getId(),
                                                    'special_price',
                                                    $specialPriceOld,
                                                    $specialPriceNew,
                                                    Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_OBSERVER );

                  if ($rez !== false) {

                      foreach ($listingsIds as $listingId) {

                            Mage::getModel('M2ePro/ListingsLogs')
                                    ->addProductMessage( $listingId,
                                                         $productNew->getId(),
                                                         Ess_M2ePro_Model_ListingsLogs::INITIATOR_EXTENSION,
                                                         NULL,
                                                         Ess_M2ePro_Model_ListingsLogs::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE,
                                                         // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                                                         Mage::getModel('M2ePro/LogsBase')->encodeDescription('From [%from%] to [%to%]',array('!from'=>$specialPriceOld,'!to'=>$specialPriceNew)),
                                                         Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE,
                                                         Ess_M2ePro_Model_ListingsLogs::PRIORITY_LOW );
                      }
                  }
                  //--------------------

                  // Save changes for special price from date
                  //--------------------
                  $specialPriceFromDateOld = $this->_productSpecialPriceFromDate;
                  $specialPriceFromDateNew = $productNew->getSpecialFromDate();

                  $rez = Mage::getModel('M2ePro/ProductsChanges')
                                ->updateAttribute(  $productNew->getId(),
                                                    'special_price_from_date',
                                                    $specialPriceFromDateOld,
                                                    $specialPriceFromDateNew,
                                                    Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_OBSERVER );

                  if ($rez !== false) {

                      if (is_null($specialPriceFromDateOld) || $specialPriceFromDateOld === false || $specialPriceFromDateOld == '') {
                          $specialPriceFromDateOld = 'None';
                      }

                      if (is_null($specialPriceFromDateNew) || $specialPriceFromDateNew === false || $specialPriceFromDateNew == '') {
                          $specialPriceFromDateNew = 'None';
                      }

                      foreach ($listingsIds as $listingId) {

                          Mage::getModel('M2ePro/ListingsLogs')
                                    ->addProductMessage( $listingId,
                                                         $productNew->getId(),
                                                         Ess_M2ePro_Model_ListingsLogs::INITIATOR_EXTENSION,
                                                         NULL,
                                                         Ess_M2ePro_Model_ListingsLogs::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE,
                                                         // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                                                         // Parser hack -> Mage::helper('M2ePro')->__('None');
                                                         Mage::getModel('M2ePro/LogsBase')->encodeDescription('From [%from%] to [%to%]',array('!from'=>$specialPriceFromDateOld,'!to'=>$specialPriceFromDateNew)),
                                                         Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE,
                                                         Ess_M2ePro_Model_ListingsLogs::PRIORITY_LOW );
                      }
                  }
                  //--------------------

                  // Save changes for special price to date
                  //--------------------
                  $specialPriceToDateOld = $this->_productSpecialPriceToDate;
                  $specialPriceToDateNew = $productNew->getSpecialToDate();

                  $rez = Mage::getModel('M2ePro/ProductsChanges')
                                ->updateAttribute(  $productNew->getId(),
                                                    'special_price_to_date',
                                                    $specialPriceToDateOld,
                                                    $specialPriceToDateNew,
                                                    Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_OBSERVER );

                  if ($rez !== false) {

                      if (is_null($specialPriceToDateOld) || $specialPriceToDateOld === false || $specialPriceToDateOld == '') {
                          $specialPriceToDateOld = 'None';
                      }

                      if (is_null($specialPriceToDateNew) || $specialPriceToDateNew === false || $specialPriceToDateNew == '') {
                          $specialPriceToDateNew = 'None';
                      }

                      foreach ($listingsIds as $listingId) {

                          Mage::getModel('M2ePro/ListingsLogs')
                                    ->addProductMessage( $listingId,
                                                         $productNew->getId(),
                                                         Ess_M2ePro_Model_ListingsLogs::INITIATOR_EXTENSION,
                                                         NULL,
                                                         Ess_M2ePro_Model_ListingsLogs::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE,
                                                         // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                                                         // Parser hack -> Mage::helper('M2ePro')->__('None');
                                                         Mage::getModel('M2ePro/LogsBase')->encodeDescription('From [%from%] to [%to%]',array('!from'=>$specialPriceToDateOld,'!to'=>$specialPriceToDateNew)),
                                                         Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE,
                                                         Ess_M2ePro_Model_ListingsLogs::PRIORITY_LOW );
                      }
                  }
                  //--------------------

                  // Save changes for custom attributes
                  //--------------------
                  $magentoProductModel = Mage::getModel('M2ePro/MagentoProduct')->setProduct($productNew);

                  foreach ($this->_productCustomAttributes as $attribute) {

                      $customAttributeOld = $attribute['value_old'];
                      $customAttributeNew = $magentoProductModel->getAttributeValue($attribute['attribute']);

                      $rez = Mage::getModel('M2ePro/ProductsChanges')
                                    ->updateAttribute(  $productNew->getId(),
                                                        $attribute['attribute'],
                                                        $customAttributeOld,
                                                        $customAttributeNew,
                                                        Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_OBSERVER );

                      if ($rez !== false) {

                          $customAttributeOld = $this->cutAttributeLength($customAttributeOld);
                          $customAttributeNew = $this->cutAttributeLength($customAttributeNew);

                          foreach ($attribute['listings'] as $listingId) {

                                 Mage::getModel('M2ePro/ListingsLogs')
                                        ->addProductMessage( $listingId,
                                                             $productNew->getId(),
                                                             Ess_M2ePro_Model_ListingsLogs::INITIATOR_EXTENSION,
                                                             NULL,
                                                             Ess_M2ePro_Model_ListingsLogs::ACTION_CHANGE_CUSTOM_ATTRIBUTE,
                                                             // Parser hack -> Mage::helper('M2ePro')->__('Attribute "%attr%" from [%from%] to [%to%]');
                                                             Mage::getModel('M2ePro/LogsBase')->encodeDescription('Attribute "%attr%" from [%from%] to [%to%]',array('!attr'=>$attribute['attribute'],'!from'=>$customAttributeOld,'!to'=>$customAttributeNew)),
                                                             Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE,
                                                             Ess_M2ePro_Model_ListingsLogs::PRIORITY_LOW );
                              }
                      }
                  }
                  //--------------------

                  // Update listings products variations
                  //--------------------
                  foreach ($listingsIds as $listingId) {

                      $listingsProductsTemp = Mage::getModel('M2ePro/Listings')
                                                        ->loadInstance($listingId)
                                                        ->getListingsProducts(true,array('product_id'=>$productNew->getId()));

                      foreach ($listingsProductsTemp as $listingProductTemp) {
                          Mage::helper('M2ePro/Variations')->updateVariations($listingProductTemp);
                      }
                  }
                  //--------------------
            }
            
            // Synch changes for categories
            //--------------------
            $categoriesNew = array_keys($productNew->getCategoryCollection()->exportToArray());

            $addedCategories = array_diff($categoriesNew,$this->_productCategoriesOld);
            foreach ($addedCategories as $categoryId) {
               Ess_M2ePro_Model_Observer_Category::synchChangesWithListings($categoryId,$productNew->getStoreId(),array($productNew->getId()),array());
            }

            $deletedCategories = array_diff($this->_productCategoriesOld,$categoriesNew);
            foreach ($deletedCategories as $categoryId) {
               Ess_M2ePro_Model_Observer_Category::synchChangesWithListings($categoryId,$productNew->getStoreId(),array(),array($productNew->getId()));
            }
            //--------------------

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}

            return;
        }
    }

    //-----------------------------------

    public function catalogProductDeleteBefore(Varien_Event_Observer $observer)
    {
        try {

            $productDeleted = $observer->getEvent()->getProduct();

            if (!($productDeleted instanceof Mage_Catalog_Model_Product)) {
                return;
            }

            $listingsIds = Mage::getResourceModel('M2ePro/Listings')->getListingsWhereIsProduct($productDeleted->getId());

            if (count($listingsIds) > 0) {

                 $rez = Mage::getModel('M2ePro/ProductsChanges')->addDeleteAction($productDeleted->getId(),
                                                                                  Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_OBSERVER);

                 if ($rez !== false) {

                      foreach ($listingsIds as $listingId) {

                             Mage::getModel('M2ePro/ListingsLogs')
                                    ->addProductMessage( $listingId,
                                                         $productDeleted->getId(),
                                                         Ess_M2ePro_Model_ListingsLogs::INITIATOR_EXTENSION,
                                                         NULL,
                                                         Ess_M2ePro_Model_ListingsLogs::ACTION_DELETE_PRODUCT_FROM_MAGENTO,
                                                         NULL,
                                                         Ess_M2ePro_Model_ListingsLogs::TYPE_WARNING,
                                                         Ess_M2ePro_Model_ListingsLogs::PRIORITY_HIGH );
                      }
                 }
            }

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}

            return;
        }
    }

    //####################################

    private function getCustomAttributes($listingsIds)
    {
        try {

            $attributes = array();

            foreach ($listingsIds as $listingId) {

                /** @var $listingModel Ess_M2ePro_Model_Listings */
                $listingModel = Mage::getModel('M2ePro/Listings')->loadInstance($listingId);

                $tempAttributesSellingFormatTemplate = $listingModel->getSellingFormatTemplate()->getUsedAttributes();
                $tempAttributesListingTemplate = $listingModel->getListingTemplate()->getUsedAttributes();
                $tempAttributesDescriptionTemplate = $listingModel->getDescriptionTemplate()->getUsedAttributes();

                $tempListingAttributes = array_merge($tempAttributesListingTemplate,$tempAttributesSellingFormatTemplate);
                $tempListingAttributes = array_merge($tempListingAttributes,$tempAttributesDescriptionTemplate);
                $tempListingAttributes = array_unique($tempListingAttributes);

                foreach ($tempListingAttributes as $attribute) {

                    $hash = md5($attribute);

                    if (!isset($attributes[$hash])) {
                        $attributes[$hash] = array(
                            'attribute' => $attribute,
                            'listings' => array($listingId)
                        );
                    } else {
                        $attributes[$hash]['listings'][] = $listingId;
                    }
                }
            }

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}

            return array();
        }

        return array_values($attributes);
    }

    private function cutAttributeLength($attribute, $length = 50)
    {
        if (strlen($attribute) > $length) {
            return substr($attribute, 0, $length) . ' ...';
        }

        return $attribute;
    }

    //####################################
}