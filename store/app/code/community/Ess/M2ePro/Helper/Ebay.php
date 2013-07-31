<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Ebay extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function getEbayItemUrl($ebayItemId, $accountMode = Ess_M2ePro_Model_Accounts::MODE_PRODUCTION, $marketplaceId = NULL)
    {
        if (is_null($marketplaceId)) {
            $marketplaceId = 0;
        } else {
            $marketplaceId = (int)$marketplaceId;
        }

        $domain = '';
        
        switch($accountMode) {
            case Ess_M2ePro_Model_Accounts::MODE_PRODUCTION:
                $domain = $this->getMarketplace($marketplaceId)->getUrl();
                break;

            case Ess_M2ePro_Model_Accounts::MODE_SANDBOX:
            default:
                $domain = 'sandbox.' . $this->getMarketplace($marketplaceId)->getUrl();
                break;
        }

        if ($marketplaceId != 100) {
            $domain = 'cgi.' . $domain;
        }

        return 'http://'.$domain.'/ws/eBayISAPI.dll?ViewItem&item='.(double)$ebayItemId;
    }

    public function getEbayMemberUrl($ebayMemberId, $accountMode = Ess_M2ePro_Model_Accounts::MODE_PRODUCTION)
    {
        $domain = '';

        switch($accountMode) {
            case Ess_M2ePro_Model_Accounts::MODE_PRODUCTION:
                $domain = 'ebay.com';
                break;

            case Ess_M2ePro_Model_Accounts::MODE_SANDBOX:
            default:
                $domain = 'sandbox.ebay.com';
                break;
        }

        return 'http://myworld.'.$domain.'/'.(string)$ebayMemberId;
    }

    // ########################################

    private function getMarketplace($marketplaceId)
    {
        $marketplaceId = (int)$marketplaceId;

        $cacheKey = Mage::helper('M2ePro/Module')->getName().'_MARKETPLACE_DATA_'.$marketplaceId;
        $cacheData = Mage::app()->getCache()->load($cacheKey);

        if ($cacheData === false) {
            $cacheData = Mage::getModel('M2ePro/Marketplaces')->load($marketplaceId);
            if (is_null($cacheData->getId())) {
                throw new Exception('Such marketplace does not exist!');
            }
            Mage::app()->getCache()->save(serialize($cacheData), $cacheKey, array(), 60*60*24);
        } else {
            $cacheData = unserialize($cacheData);
        }

        return $cacheData;
    }

    // ########################################
}