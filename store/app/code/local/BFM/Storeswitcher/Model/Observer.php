<?php

class BFM_Storeswitcher_Model_Observer
{
	public function setCurrency()
	{
		if($currency = Mage::app()->getRequest()->getParam('___currency', false))
		{
			$currency = strtoupper($currency);			
			if (in_array($currency, Mage::app()->getStore()->getAvailableCurrencyCodes())) {
				Mage::app()->getStore()->setCurrentCurrencyCode($currency);
				Mage::getSingleton('customer/session')->setCurrency($currency);
				Mage::getModel('core/cookie')->set('currency', $currency);
				return;
			}
		}
		
		if(Mage::getSingleton('customer/session')->hasCurrency() && $currency = Mage::getSingleton('customer/session')->getCurrency())
		{
			if (in_array($currency, Mage::app()->getStore()->getAvailableCurrencyCodes())) {
				Mage::app()->getStore()->setCurrentCurrencyCode($currency);
				Mage::getSingleton('customer/session')->setCurrency($currency);
				Mage::getModel('core/cookie')->set('currency', $currency);
				return;
			}
		}
		
		if($currency = Mage::getModel('core/cookie')->get('currency'))
		{
			if (in_array($currency, Mage::app()->getStore()->getAvailableCurrencyCodes())) {
				Mage::app()->getStore()->setCurrentCurrencyCode($currency);
				Mage::getSingleton('customer/session')->setCurrency($currency);
				Mage::getModel('core/cookie')->set('currency', $currency);
				return;
			}
		}
	}
}