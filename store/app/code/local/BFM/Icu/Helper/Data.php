<?php
class BFM_Icu_Helper_Data extends Mage_Core_Helper_Data
{
	public function backUrl()
	{
		$baseUrl = Mage::getStoreConfig('web/unsecure/base_url');
		$baseSecureUrl = Mage::getStoreConfig('web/secure/base_url');
		$url = $_SERVER['HTTP_REFERER'];
		
		if ((mb_strpos($url, $baseUrl) === false) || (mb_strpos($url, $baseSecureUrl) === false)) {
			$url = false;
		} 
		
		return $url;
	}
}