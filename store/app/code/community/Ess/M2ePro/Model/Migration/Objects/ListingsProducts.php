<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_ListingsProducts extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e_product_to_project';
    const TABLE_NAME_NEW = 'm2epro_listings_products';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_ListingsProducts');
    }

    // ########################################

	public function process()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameOld,'*')
                                              ->where('`item_is_relisted` = 0');

        /** @var $pdoStmt Zend_Db_Statement_Interface */
        $pdoStmt = $this->mySqlReadConnection->query($dbSelect);
        $pdoStmt->setFetchMode(Zend_Db::FETCH_ASSOC);

        while ($oldListingProduct = $pdoStmt->fetch()) {

            $listingId = $this->tempDbTable->getNewValue('listings.id',(int)$oldListingProduct['project_id']);
            if ($listingId === false) {
                continue;
            }

            $ebayItemsId = NULL;
            if (!is_null($oldListingProduct['item_id']) && (double)$oldListingProduct['item_id'] > 0) {
                $ebayItemsId = $this->tempDbTable->getNewValue('ebay_items.id',(int)$oldListingProduct['id']);
                if ($ebayItemsId === false) {
                    continue;
                }
                $ebayItemsId = (int)$ebayItemsId;
            }

            $status = (int)$oldListingProduct['status'];
            $status == -1 && $status = Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED;
            $status == 5 && $status = Ess_M2ePro_Model_ListingsProducts::STATUS_FINISHED;

            $newListingProduct = array(
                'listing_id' => (int)$listingId,
                'product_id' => (int)$oldListingProduct['product_id'],

                'ebay_items_id' => $status == 0 ? NULL : $ebayItemsId,

                'ebay_start_price' => $status == 0 ? NULL : (float)$oldListingProduct['ebay_price'],
                'ebay_reserve_price' => $status == 0 ? NULL : (float)$oldListingProduct['ebay_resevreprice'],
                'ebay_buyitnow_price' => $status == 0 ? NULL : (float)$oldListingProduct['ebay_buyitnow'],

                'ebay_qty' => $status == 0 ? NULL : (int)$oldListingProduct['ebay_qty'],
                'ebay_qty_sold' => $status == 0 ? NULL : (int)$oldListingProduct['qty_sold'],
                'ebay_bids' => $status == 0 ? NULL : (int)$oldListingProduct['bids'],

                'ebay_start_date' => $status == 0 ? NULL : $oldListingProduct['action_time'],
                'ebay_end_date' => NULL,

                'status' => (int)$status,
                'status_changer' => Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_UNKNOWN
            );

            /** @var $newListingModel Ess_M2ePro_Model_Listings */
            $newListingModel = Mage::getModel('M2ePro/Listings')->loadInstance($listingId);

            if (!$newListingModel->getSellingFormatTemplate()->isListingTypeAuction()) {
                $newListingProduct['ebay_reserve_price'] = NULL;
                $newListingProduct['ebay_buyitnow_price'] = NULL;
                $newListingProduct['ebay_bids'] = NULL;
            }

            $existListingProduct = $this->getLikeExistItem($newListingProduct,false);
            if (!is_null($existListingProduct)) {
                $this->tempDbTable->addValue('listings_products.id',(int)$oldListingProduct['id'],(int)$existListingProduct['id']);
            } else {
                $newListingProduct['create_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $newListingProduct['update_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $this->mySqlWriteConnection->insert($this->tableNameNew,$newListingProduct);
                $newListingProductId = $this->mySqlWriteConnection->lastInsertId($this->tableNameNew,'id');
                $this->tempDbTable->addValue('listings_products.id',(int)$oldListingProduct['id'],(int)$newListingProductId);

                $listingProductTemp = Mage::getModel('M2ePro/ListingsProducts')->loadInstance((int)$newListingProductId);
                Mage::helper('M2ePro/Variations')->updateVariations($listingProductTemp);

                if (!is_null($oldListingProduct['variation_info']) && $oldListingProduct['variation_info'] != '') {
                    try {
                        $variationInfo = (array)@unserialize($oldListingProduct['variation_info']);
                        foreach ($variationInfo as $variationOld) {
                            /** @var $variationModelNew Ess_M2ePro_Model_ListingsProductsVariations */
                            $variationModelNew = $this->getNewVariationByOld($listingProductTemp,$variationOld);
                            if ($variationModelNew === false) {
                                continue;
                            }
                            $dataForUpdate = array(
                                'ebay_price' => $variationModelNew->getPrice(),
                                'ebay_qty' => (int)$variationOld['ebay_qty'],
                                'ebay_qty_sold' => (int)$variationOld['qty_sold'],
                                'status' => (int)$variationOld['ebay_qty'] > 0 ? Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED : Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED
                            );
                            $variationModelNew->addData($dataForUpdate)->save();
                        }
                   } catch (Exception $exception) {}
                }
            }
        }
    }

    // ########################################

    public function getNewVariationByOld(Ess_M2ePro_Model_ListingsProducts $listingProduct, array $variationOld)
    {
        if (!isset($variationOld['specifics']) || !is_array($variationOld['specifics']) ||
            count($variationOld['specifics']) <= 0) {
            return false;
        }

        $variationsModels = $listingProduct->getListingsProductsVariations(true);

        if (count($variationsModels) <= 0) {
            return false;
        }

        foreach ($variationsModels as $variationModel) {

            /** @var $variationModel Ess_M2ePro_Model_ListingsProductsVariations */
            $options = $variationModel->getListingsProductsVariationsOptions(false);
            if (count($options) != count($variationOld['specifics'])) {
                continue;
            }
            
            $equalVariations = true;
            foreach ($options as $optionNew) {
                $haveOption = false;
                foreach ($variationOld['specifics'] as $optionOldKey => $optionOldValue) {
                    if ($optionNew['attribute'] == $optionOldKey && $optionNew['option'] == $optionOldValue) {
                        $haveOption = true;
                        break;
                    }
                }
                if (!$haveOption) {
                    $equalVariations = false;
                    break;
                }
            }
            if ($equalVariations) {
                return $variationModel;
            }
        }

        return false;
    }

    // ########################################
}