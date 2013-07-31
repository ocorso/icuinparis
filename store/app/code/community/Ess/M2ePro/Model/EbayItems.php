<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_EbayItems extends Mage_Core_Model_Abstract
{
    // ########################################
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/EbayItems');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_EbayItems
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('eBay item does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingProductId
     * @return Ess_M2ePro_Model_EbayItems
     */
    public function loadByListingProduct($listingProductId)
    {
         $tempModel = Mage::getModel('M2ePro/ListingsProducts')->load($listingProductId);

         if (is_null($tempModel->getId())) {
             throw new Exception('Listing product does not exist. Probably it was deleted.');
         }

         if (!$tempModel->getData('ebay_items_id')) {
             throw new Exception('eBay items are not set for listing product');
         }

         return $this->loadInstance($tempModel->getData('ebay_items_id'));
    }

    // ########################################

    /**
     * @return bool
     */
    public function isLocked()
    {
        if (!$this->getId()) {
            return false;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->delete();
        return true;
    }

    // ########################################

    public function getItemId()
    {
        return (double)$this->getData('item_id');
    }

    public function getProductId()
    {
        return (int)$this->getData('product_id');
    }

    public function getStoreId()
    {
        return (int)$this->getData('store_id');
    }

    // ########################################
}