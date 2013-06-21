<?php
class BFM_Storeswitcher_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getCurrencies()
	{
		 $currs = Mage::app()->getStore()->getAvailableCurrencyCodes();
		 $base_curr = Mage::app()->getStore()->getBaseCurrencyCode();
		 $base_curr_model = $this->getCurrency($base_curr);
		 
		 foreach($currs as $k => $curr)
		 {
		 	if(!$base_curr_model->getRate($curr))
		 	{
		 		unset($currs[$k]);
		 	}
		 }
		 return $currs;
		 
	}
	
	public function getCurrentCurrency()
	{
		return Mage::app()->getStore()->getCurrentCurrencyCode();
	}
	
	public function getCurrency($code)
	{
		return Mage::getModel('directory/currency')->load($code);
	}
	
	public function getCurrencyResource()
	{
		return Mage::getResourceModel('directory/currency');
	}
}
