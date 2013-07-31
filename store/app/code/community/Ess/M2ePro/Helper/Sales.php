<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Sales extends Mage_Core_Helper_Abstract
{
    protected $_defaultStoreId = 0;
    protected $_defaultWebsiteId = 0;

    // ########################################

    public function getProductFirstStoreId($product)
    {
        if (!$product->getId()) {
            return false;
        }

        $storeIdList = array_values($product->getStoreIds());
        if (count($storeIdList) < 1) {
            return false;
        }
        // Get first from all available store view
        return $storeIdList[0];
    }

    public function getDefaultWebsiteId()
    {
        if ($this->_defaultWebsiteId == 0) {
            $websiteItems = Mage::getModel('core/website')->getCollection()
                                                          ->addFieldToFilter('is_default', 1)
                                                          ->getItems();
            $websiteId = 0;
            foreach ($websiteItems as $item) {
                if ($item->getIsDefault() == 1) {
                    $websiteId = $item->getWebsiteId();
                    break;
                }
            }
            $this->_defaultWebsiteId = $websiteId;
        }
        return $this->_defaultWebsiteId;
    }

    /**
     * Calculate and return default frontent magento storeId
     *
     * @return void
     */
    public function getDefaultStoreId()
    {
        if ($this->_defaultStoreId == 0) {
            $storesGroups = Mage::getModel('core/store_group')->getCollection()
                                                              ->addFieldToFilter('website_id', $this->getDefaultWebsiteId())
                                                              ->getItems();

            $storeGroup = array_shift($storesGroups);

            if ($storeGroup != null && $storeGroup != false) {
                $this->_defaultStoreId = $storeGroup->getDefaultStoreId();
            }
        }

        // Get by sort order
        //        if ($storeId == 0) {
        //            $storeListForWebsite = Mage::getModel('core/store')->getCollection()->addFieldToFilter("website_id", $this->_webSiteId)->getData();
        //            $selectedIndex = 0;
        //            $minSortOrder = -1;
        //            foreach ($storeListForWebsite as $key => $singleStore) {
        //                if ($singleStore['sort_order'] < $minSortOrder || $minSortOrder == -1) {
        //                    $minSortOrder = $singleStore['sort_order'];
        //                    $selectedIndex = $key;
        //                }
        //            }
        //            $storeId = $storeListForWebsite[$selectedIndex]['store_id'];
        //        }
        
        return $this->_defaultStoreId;
    }

    // ########################################

    public function getEbayCarriers()
    {
        return array(
            'dhl'   => 'DHL',
            'fedex' => 'FedEx',
            'ups'   => 'UPS',
            'usps'  => 'USPS'
        );
    }

    // ########################################
}