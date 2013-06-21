<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 * Shipping method with custom title and price
 */

class Ess_M2ePro_Model_ShippingMethod extends Mage_Shipping_Model_Carrier_Abstract
{
    /**
     * Unique internal shipping method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'm2eproshipping';

    /**
     * Collect rates for this shipping method based on information in $request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        // skip if not enabled
        if (!Mage::getStoreConfig('carriers/' . $this->_code . '/active')) {
            return false;
        }

        // Get shipping value from session
        $ebayShippingData = Mage::registry('ebayShippingData'); //,$shippingDetails);
        if ($ebayShippingData == false || $ebayShippingData == null) {
            // No data in session - this is frontend.
            if (!Mage::getStoreConfig('carriers/' . $this->_code . '/show_on_frontend')) {
                // Don't show on frontend
                return false;
            }

            $ebayShippingData = array(
                'title' => 'eBay',
                'price' => 0
            );
        }

        $result = Mage::getModel('shipping/rate_result');
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);

        $method->setMethod($this->_code);

        $method->setCarrierTitle(Mage::helper('M2ePro')->__('eBay Shipping'));

        if (!isset($ebayShippingData['title'])) {
            $ebayShippingData['title'] = Mage::helper('M2ePro')->__('eBay Shipping');
        }
        $method->setMethodTitle($ebayShippingData['title']);

        if (!isset($ebayShippingData['price'])) {
            $ebayShippingData['price'] = 0.0;
        }

        $method->setCost($ebayShippingData['price']);
        $method->setPrice($ebayShippingData['price']);

        $result->append($method);
        return $result;
    }

    public function checkAvailableShipCountries(Mage_Shipping_Model_Rate_Request $request)
    {
        // Get shipping value from session
        $ebayShippingData = Mage::registry('ebayShippingData');

        if (!$ebayShippingData) {
            // No data in session - this is frontend.
            if (!Mage::getStoreConfig('carriers/' . $this->_code . '/show_on_frontend')) {
                // Don't show on frontend
                return false;
            }
        }

        return parent::checkAvailableShipCountries($request);
    }
}