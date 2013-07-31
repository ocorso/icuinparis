<?php
class BFM_MerchantFeed_Block_Feed extends Mage_Core_Block_Template {

	protected $_configToChange;
	protected $_currentCurrency;
	protected $_storeCurrency;
	
	protected $_categoryCollection = array();
	
	protected function _construct()
	{
		parent::_construct();
		$key = 'bfm_merchantinfo_' . $this->getRequest()->getParam('currency');
		$this
			->setCacheLifetime(false)
			->setCacheKey($key)
			->setCacheTags(array(
				Mage_Catalog_Model_Product::CACHE_TAG,
				Mage_Catalog_Model_Category::CACHE_TAG
			));
	}
	
	protected function _prepareLayout() {
		
		parent::_prepareLayout();
		
		Mage::app()->getStore()->setId(0);
		
		$collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToFilter('status', 1)
			->addAttributeToFilter('visibility', 4)
			->addAttributeToSelect('sku')
			->addAttributeToSelect('name')
			->addAttributeToSelect('description')
			->addAttributeToSelect('short_description')
			->addAttributeToSelect('url')
			->addAttributeToSelect('image')
			->addAttributeToSelect('price')
			->addAttributeToSelect('special_price')
			->addAttributeToSelect('manufacturer')
			->addAttributeToSelect('category_ids')
			->addAttributeToSelect('size')
			->addAttributeToSelect('color')
			->addAttributeToSelect('url_path');
		
		$collection->getSelect()->joinLeft(
			array('stock' => 'cataloginventory_stock_item'),
			'stock.product_id = e.entity_id',
			array('stock.qty', 'stock.is_in_stock')
		)->where('stock.qty > 0 AND stock.is_in_stock = 1');
		
		$this->setCollection($collection);
		
		$this->_storeCurrency   = Mage::app()->getDefaultStoreView()->getBaseCurrency();
		$this->_currentCurrency = Mage::getModel('directory/currency')->load($this->getRequest()->getParam('currency'));
		
		Mage::app()->getResponse()->setHeader('Content-Type', 'application/xml');
		
		return $this;
	}
	
	public function getFeedTitle() {
		return Mage::getStoreConfig('merchantfeed_settings/general/title');
	}
	
	public function getFeedLink() {
		return Mage::getStoreConfig('merchantfeed_settings/general/link');
	}
	
	public function getFeedDescription() {
		return Mage::getStoreConfig('merchantfeed_settings/general/description');
	}
	
	public function getItemHtml(Mage_Catalog_Model_Product $product) {
		$productData = new Varien_Object();
		
		$description = $product->getDescription();
		$imageUrl    = (string)Mage::helper('catalog/image')->init($product, 'image')->resize(265);
		$brand       = Mage::app()->getStore()->getName();
		
		$categoryCollection = Mage::getModel('catalog/category')->getCollection()
			->addNameToResult()
			->addIdFilter($product->getCategoryIds());

		foreach($categoryCollection as $category) {
			$categories[] = $category->getName();
		}
		
		$attrColor = '';
		if ($product->getResource()->getAttribute('color')) {
			$attrColor = $product->getAttributeText('color') ? $product->getAttributeText('color') : '';
		}
		
		$productType = implode(', ', $categories);
		
		$productData->setData(array(
			'title'           => $product->getName(),
			'link'            => $this->_getUrlWithCategory($product),
			'description'     => $description,
			'id'              => $product->getId(),
			'condition'       => 'new',
			'price'           => htmlspecialchars($this->_getPrice($product)),
			'availability'    => 'In Stock',
			'image_link'      => $imageUrl,
			'brand'           => htmlspecialchars($brand),
			'mpn'             => htmlspecialchars($product->getSku()),
			'product_type'    => htmlspecialchars($productType),
			'google_category' => 'Apparel & Accessories > Jewelry',
			'gender'          => $this->_getGender($categories),
			'color'           => $attrColor,
			'size'			  => $product->getSize(),
			'age_group'       => 'Adult',
		));
		
		return $this->getChild('merchantfeed_feed_item')->setProductData($productData)->toHtml();
	}
	
	protected function _getPrice(Mage_Catalog_Model_Product $product) {
		return round($this->_storeCurrency->convert($product->getFinalPrice(), $this->_currentCurrency), 2) . ' ' . $this->_currentCurrency->getCode();
	}
	
	protected function _getGender(array $categories) {
		return in_array('Ladies', $categories) ? 'Female' : 'Male';
	}
	
	protected function _getUrlWithCategory($product)
	{
		$ids = $product->getCategoryIds();
		
		if (is_array($ids) && count($ids) > 0) {
			$id = $ids[0];
			
			if (Mage::app()->getStore()->getRootCategoryId() == $ids[0] && count($ids) > 1) {
				$id = $ids[1];
			} else {
				return Mage::getStoreConfig('web/unsecure/base_url') . $product->getUrlPath();
			}
			
			$category = $this->_getCategory($id);
			$url = Mage::getStoreConfig('web/unsecure/base_url') . $product->getUrlPath($category);
		} else {
			$url = Mage::getStoreConfig('web/unsecure/base_url') . $product->getUrlPath();
		}
		
		return $url;
	}
	
	public function _getCategory($id)
	{
		if (!array_key_exists($id, $this->_categoryCollection)) {
			$this->_categoryCollection[$id] = Mage::getModel('catalog/category')->load($id);
		}
		return $this->_categoryCollection[$id];
	}
}