<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_StockItem
{
    //####################################

    public function catalogInventoryStockItemSaveAfter(Varien_Event_Observer $observer)
    {
        try {

             // Get product id
             $productId = $observer->getData('item')->getData('product_id');

             // Get listing where is product
             $listingsIds = Mage::getResourceModel('M2ePro/Listings')->getListingsWhereIsProduct($productId);

             if (count($listingsIds) > 0) {

                    // Save global changes
                    //--------------------
                    Mage::getModel('M2ePro/ProductsChanges')
                                ->updateAttribute( $productId,
                                                   'product_instance',
                                                   'any_old',
                                                   'any_new',
                                                    Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_OBSERVER );
                    //--------------------
                 
                    // Save changes for qty
                    //--------------------
                    $qtyOld = (int)$observer->getData('item')->getOrigData('qty');
                    $qtyNew = (int)$observer->getData('item')->getData('qty');

                    $rez = Mage::getModel('M2ePro/ProductsChanges')
                                ->updateAttribute(  $productId,
                                                    'qty',
                                                    $qtyOld,
                                                    $qtyNew,
                                                    Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_OBSERVER );

                    if ($rez !== false) {

                          foreach ($listingsIds as $listingId) {

                                 Mage::getModel('M2ePro/ListingsLogs')
                                        ->addProductMessage( $listingId,
                                                             $productId,
                                                             Ess_M2ePro_Model_ListingsLogs::INITIATOR_EXTENSION,
                                                             NULL,
                                                             Ess_M2ePro_Model_ListingsLogs::ACTION_CHANGE_PRODUCT_QTY,
                                                             // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                                                             Mage::getModel('M2ePro/LogsBase')->encodeDescription('From [%from%] to [%to%]',array('!from'=>$qtyOld,'!to'=>$qtyNew)),
                                                             Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE,
                                                             Ess_M2ePro_Model_ListingsLogs::PRIORITY_LOW );
                          }
                    }
                    //--------------------

                    // Save changes for stock Availability
                    //--------------------
                    $stockAvailabilityOld = (bool)$observer->getData('item')->getOrigData('is_in_stock');
                    $stockAvailabilityNew = (bool)$observer->getData('item')->getData('is_in_stock');

                    $rez = Mage::getModel('M2ePro/ProductsChanges')
                                    ->updateAttribute(  $productId,
                                                        'stock_availability',
                                                        (int)$stockAvailabilityOld,
                                                        (int)$stockAvailabilityNew,
                                                        Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_OBSERVER );

                    if ($rez !== false) {

                          $stockAvailabilityOld = $stockAvailabilityOld ? 'IN Stock' : 'OUT of Stock';
                          $stockAvailabilityNew = $stockAvailabilityNew ? 'IN Stock' : 'OUT of Stock';

                          foreach ($listingsIds as $listingId) {

                                 Mage::getModel('M2ePro/ListingsLogs')
                                        ->addProductMessage( $listingId,
                                                             $productId,
                                                             Ess_M2ePro_Model_ListingsLogs::INITIATOR_EXTENSION,
                                                             NULL,
                                                             Ess_M2ePro_Model_ListingsLogs::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                                                             // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                                                             // Parser hack -> Mage::helper('M2ePro')->__('IN Stock');
                                                             // Parser hack -> Mage::helper('M2ePro')->__('OUT of Stock');
                                                             Mage::getModel('M2ePro/LogsBase')->encodeDescription('From [%from%] to [%to%]',array('from'=>$stockAvailabilityOld,'to'=>$stockAvailabilityNew)),
                                                             Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE,
                                                             Ess_M2ePro_Model_ListingsLogs::PRIORITY_LOW );
                          }
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

    //####################################
}