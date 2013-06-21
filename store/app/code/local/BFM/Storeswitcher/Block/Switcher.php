<?php
class BFM_Storeswitcher_Block_Switcher extends Mage_Core_Block_Template
{
    protected $_groups = array();
    protected $_stores = array();
    protected $_loaded = false;
    protected $_current_website;
    protected $_current_store;
    protected $_current_store_code;
    
	protected function _construct()
	{
		$this->setTemplate('bfm_storeswitcher/switcher.phtml');
		return parent::_construct();
	}
	
	protected function _loadData()
	{
		if($this->_loaded) return $this;
		$this->_current_website = $websiteId = Mage::app()->getStore()->getWebsiteId();
		$this->_current_store_code = Mage::app()->getStore()->getCode();
        $storeCollection = Mage::getModel('core/store')
            ->getCollection()
            ->addWebsiteFilter($websiteId);
            
        $groupCollection = Mage::getModel('core/store_group')
            ->getCollection()
            ->addWebsiteFilter($websiteId);
            
        foreach ($groupCollection as $group) {
            $this->_groups[$group->getId()] = $group;
        }
        foreach ($storeCollection as $store) {
            if (!$store->getIsActive()) {
                continue;
            }
            $store->setLocaleCode(Mage::getStoreConfig('general/locale/code', $store->getId()));
            if($store->getCode() == $this->_current_store_code) $this->_current_store = $store; 
            $this->_stores[$store->getGroupId()][$store->getId()] = $store;
        }

        $this->_loaded = true;

        return $this;
	}
	
	public function getLanguages()
	{
		$this->_loadData();
		if(isset($this->_stores[$this->_current_website])) return $this->_stores[$this->_current_website];
		return array();				 
	}
	
	public function getCurrentStore()
	{
		$this->_loadData();
		return $this->_current_store;
	}
	
	public function getCurrentStoreCode()
	{
		$this->_loadData();
		return $this->_current_store_code;
	}
	
	public function getCurrentCurrency()
	{
		return $this->helper('storeswitcher')->getCurrentCurrency();
	}
	
	public function getCurrencies()
	{
		return $this->helper('storeswitcher')->getCurrencies();
	}
	
	public function getCurrency($code)
	{
		return $this->helper('storeswitcher')->getCurrency($code);
	}
	
	public function getLanguageUrl($_lang, $_current_store = null)
	{
		if(is_null($_current_store)) $_current_store = $this->getCurrentStore();			
		return $this->getBaseUrl() . '?___store=' . $_lang->getCode() . '&___from_store=' . $_current_store->getCode();
	}
	
	public function getCurrencyUrl($_currency)
	{
		$current_url = $this->helper('core/url')->getCurrentUrl();
		$prefix = '?';
		if(strpos($current_url, '?') !== false)
		{
			$current_url = preg_replace('/___currency=[^&]*(\?|&)?/', '', $current_url);
			if(strpos($current_url, '?') !== false) $prefix = '&'; 
		}
					
		return $current_url . $prefix . '___currency=' . $_currency;
	}
	
}