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
 * Canonical url's Head block.
 *
 * @category    BFM
 * @package     BFM_CanonicalUrl
 * @author      Blue Fountain Media <magento@bluefountainmedia.com>
 */
class BFM_CanonicalUrl_Block_Head extends Mage_Page_Block_Html_Head
{

    public function getHeadUrl()
    {
        if (empty($this->_data['urlKey']))
        {
            $host = parse_url(Mage::helper('core/url')->getCurrentUrl(), PHP_URL_HOST);
            $path = parse_url(Mage::helper('core/url')->getCurrentUrl(), PHP_URL_PATH);
            $path = preg_replace('!\/+!', '/', $path);
            $headUrl = "http://{$host}{$path}";
            
            if (Mage::getStoreConfig('canonicalurl/settings/endslash'))
            {
                if (! preg_match('/\\.(' . self::_getExtList() . '?)$/', strtolower($headUrl)) && substr($headUrl, - 1) != '/')
                {
                    $headUrl .= '/';
                }
            }
            
            $this->_data['urlKey'] = $headUrl;
        }
        
        return $this->_data['urlKey'];
    }

    public function getHeadProductUrl()
    {
        $storeId = $this->getStoreId();
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        $productId = $this->getRequest()->getParam('id');
        
        $product = Mage::getResourceModel('sitemap/catalog_product')->getProduct($productId, $storeId);
        
        if ($product === NULL)
        {
            $this->_data['urlKey'] = NULL;
        }
        else
        {
            $headUrl = $baseUrl . $product->getUrl();
            if (Mage::getStoreConfig('canonicalurl/settings/endslash'))
            {
                if (! preg_match('/\\.(' . self::_getExtList() . '?)$/', strtolower($headUrl)) && substr($headUrl, - 1) != '/')
                {
                    $headUrl .= '/';
                }
            }
            
            $this->_data['urlKey'] = $headUrl;
        }
        
        return $this->_data['urlKey'];
    }

    protected static function _getExtList()
    {
        $extList = 'rss|html|htm|xml|php';
        $productsSuffix = Mage::getStoreConfig('catalog/seo/product_url_suffix');
        if (strlen($productsSuffix))
        {
            if (substr($productsSuffix, 0, 1) == '.')
                $productsSuffix = substr($productsSuffix, 1, strlen($productsSuffix));
            
            $extList .= '|' . $productsSuffix;
        }
        
        $categorySuffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
        if (strlen($categorySuffix))
        {
            if (substr($categorySuffix, 0, 1) == '.')
                $categorySuffix = substr($categorySuffix, 1, strlen($categorySuffix));
            
            $extList .= '|' . $categorySuffix;
        }
        return $extList;
    }
}