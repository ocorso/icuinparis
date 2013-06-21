<?php 
/**
 * Blue Fountain Media
 *
 * NOTICE OF LICENSE
 *
 * <notice_of_license>
 *
 * @category    BFM
 * @package     BFM_CanonicalUrl
 * @copyright   Copyright (c) 2011 Blue Fountain Media (http://www.bluefountainmedia.com/). All Rights Reserved.
 * @license     <license_url>
 */

/**
 * Model overrides a system Model of Sitemap module.
 * This class extends base functionality with method to fetch the one product for Canonical url.
 *
 * @category    BFM
 * @package     BFM_CanonicalUrl
 * @author      Blue Fountain Media <magento@bluefountainmedia.com>
 */
class BFM_CanonicalUrl_Model_Sitemap_Catalog_Product extends Mage_Sitemap_Model_Mysql4_Catalog_Product
{
    /**
     * Get category collection array
     *
     * @param integer $storeId
     * @return array
     */
    public function getProduct($productId, $storeId)
    {
        try 
        {
            $store = Mage::app()->getStore($storeId);
            /* @var $store Mage_Core_Model_Store */
    
            if (!$store) {
                return false;
            }
    
            $urCondions = array(
                'e.entity_id=ur.product_id',
                'ur.category_id IS NULL',
                $this->_getWriteAdapter()->quoteInto('ur.store_id=?', $store->getId()),
                $this->_getWriteAdapter()->quoteInto('ur.is_system=?', 1),
            );
            $this->_select = $this->_getWriteAdapter()->select()
                ->from(array('e' => $this->getMainTable()), array($this->getIdFieldName()))
                ->join(
                    array('w' => $this->getTable('catalog/product_website')),
                    'e.entity_id=w.product_id',
                    array()
                )
                ->where('e.entity_id=?', (int)$productId)
                ->where('w.website_id=?', $store->getWebsiteId())
                ->joinLeft(
                    array('ur' => $this->getTable('core/url_rewrite')),
                    join(' AND ', $urCondions),
                    array('url' => 'request_path')
                );
    
            $this->_addFilter($storeId, 'visibility', Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds(), 'in');
            $this->_addFilter($storeId, 'status', Mage::getSingleton('catalog/product_status')->getVisibleStatusIds(), 'in');
    
            $query = $this->_getWriteAdapter()->query($this->_select);
            $row = $query->fetch();
            $product = $this->_prepareProduct($row);
        }
        catch (Exception $e)
        {
            $product = NULL;
        }
        return $product;
    }
}