<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_SellingFormatTemplates extends Mage_Core_Model_Abstract
{
	const LISTING_TYPE_AUCTION      = 1;
	const LISTING_TYPE_FIXED        = 2;
	const LISTING_TYPE_ATTRIBUTE    = 3;

    const EBAY_LISTING_TYPE_AUCTION  = 'Chinese';
	const EBAY_LISTING_TYPE_FIXED    = 'FixedPriceItem';

    const LISTING_IS_PRIVATE_NO   = 0;
    const LISTING_IS_PRIVATE_YES  = 1;

    const DURATION_TYPE_EBAY       = 1;
    const DURATION_TYPE_ATTRIBUTE  = 2;

    const QTY_MODE_PRODUCT      = 1;
	const QTY_MODE_SINGLE       = 2;
	const QTY_MODE_NUMBER       = 3;
    const QTY_MODE_ATTRIBUTE    = 4;

    const PRICE_NONE      = 0;
    const PRICE_PRODUCT   = 1;
    const PRICE_SPECIAL   = 2;
    const PRICE_ATTRIBUTE = 3;

    const PRICE_VARIATION_MODE_PARENT        = 1;
    const PRICE_VARIATION_MODE_CHILDREN      = 2;

    const BEST_OFFER_MODE_NO  = 0;
    const BEST_OFFER_MODE_YES = 1;

    const BEST_OFFER_ACCEPT_MODE_NO          = 0;
    const BEST_OFFER_ACCEPT_MODE_PERCENTAGE  = 1;
    const BEST_OFFER_ACCEPT_MODE_ATTRIBUTE   = 2;

	const BEST_OFFER_REJECT_MODE_NO          = 0;
    const BEST_OFFER_REJECT_MODE_PERCENTAGE  = 1;
    const BEST_OFFER_REJECT_MODE_ATTRIBUTE   = 2;

    // ########################################
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/SellingFormatTemplates');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_SellingFormatTemplates
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Selling Format Template does not exist. Probably it was deleted.');
        }
        
        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingId
     * @return Ess_M2ePro_Model_SellingFormatTemplates
     */
    public function loadByListing($listingId)
    {
         $tempModel = Mage::getModel('M2ePro/Listings')->load($listingId);
         
         if (is_null($tempModel->getId())) {
             throw new Exception('Listing does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('selling_format_template_id'));
    }

    // ########################################

    /**
     * @return bool
     */
    public function isLocked()
    {
        if (!$this->getId()) {
            return false;
        }

        return (bool)Mage::getModel('M2ePro/Listings')
                            ->getCollection()
                            ->addFieldToFilter('selling_format_template_id', $this->getId())
                            ->getSize();
    }

    /**
     * @return bool
     */
    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @param array $filters
     * @return array
     */
    public function getListings($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/Listings')->getCollection();
        $tempCollection->addFieldToFilter('selling_format_template_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $tempInstance = Mage::getModel('M2ePro/Listings')
                                        ->loadInstance($item['id']);
                $tempInstance->setSellingFormatTemplate($this);
                $resultArray[] = $tempInstance;
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    // ########################################

    public function parsePrice($price, $coefficient = false)
    {
        if (is_string($coefficient)) {
            $coefficient = trim($coefficient);
        }

        if ($price <= 0) {
            return 0;
        }

        if (!$coefficient) {
            return round($price, 2);
        }

        if (strpos($coefficient, '%')) {
            $coefficient = str_replace('%', '', $coefficient);

            if (preg_match('/^[+-]/', $coefficient)) {
                return round($price + $price * (float)$coefficient / 100, 2);
            }
            return round($price * (float)$coefficient / 100, 2);
        }

        if (preg_match('/^[+-]/', $coefficient)) {
            return round($price + (float)$coefficient, 2);
        }

        return round($price * (float)$coefficient, 2);
    }

    // ########################################

    public function getTitle()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('title');
    }

    public function isPrivateListing()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (bool)$this->getData('listing_is_private');
    }

    public function getCurrency()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('currency');
    }

    public function isBestOfferEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('best_offer_mode') == self::BEST_OFFER_MODE_YES;
    }

    //-------------------------

    public function getListingType()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('listing_type');
    }

    public function isListingTypeFixed()
    {
        return $this->getListingType() == self::LISTING_TYPE_FIXED;
    }

    public function isListingTypeAuction()
    {
        return $this->getListingType() == self::LISTING_TYPE_AUCTION;
    }

    public function isListingTypeAttribute()
    {
        return $this->getListingType() == self::LISTING_TYPE_ATTRIBUTE;
    }

    public function getListingTypeSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode'      => $this->getListingType(),
            'attribute' => $this->getData('listing_type_attribute')
        );
    }

    public function getListingTypeAttributes()
    {
        $attributes = array();
        $src = $this->getListingTypeSource();

        if ($src['mode'] == self::LISTING_TYPE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getDurationEbay()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('duration_ebay');
    }

    public function getDurationSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $tempSrc = $this->getListingTypeSource();

        $mode = self::DURATION_TYPE_EBAY;
        if ($tempSrc['mode'] == self::LISTING_TYPE_ATTRIBUTE) {
            $mode = self::DURATION_TYPE_ATTRIBUTE;
        }

        return array(
            'mode'     => (int)$mode,
            'value'     => (int)$this->getDurationEbay(),
            'attribute' => $this->getData('duration_attribute')
        );
    }

    public function getDurationAttributes()
    {
        $attributes = array();
        $src = $this->getDurationSource();

        if ($src['mode'] == self::DURATION_TYPE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getQtyMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('qty_mode');
    }

    public function isQtyModeProduct()
    {
        return $this->getQtyMode() == self::QTY_MODE_PRODUCT;
    }

    public function isQtyModeSingle()
    {
        return $this->getQtyMode() == self::QTY_MODE_SINGLE;
    }

    public function isQtyModeNumber()
    {
        return $this->getQtyMode() == self::QTY_MODE_NUMBER;
    }

    public function isQtyModeAttribute()
    {
        return $this->getQtyMode() == self::QTY_MODE_ATTRIBUTE;
    }

    public function getQtyNumber()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('qty_custom_value');
    }

    public function getQtySource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode'     => $this->getQtyMode(),
            'value'  => $this->getQtyNumber(),
            'attribute' => $this->getData('qty_custom_attribute')
        );
    }

    public function getQtyAttributes()
    {
        $attributes = array();
        $src = $this->getQtySource();

        if ($src['mode'] == self::QTY_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getStartPriceMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('start_price_mode');
    }

    public function isStartPriceModeNone()
    {
        return $this->getStartPriceMode() == self::PRICE_NONE;
    }

    public function isStartPriceModeProduct()
    {
        return $this->getStartPriceMode() == self::PRICE_PRODUCT;
    }

    public function isStartPriceModeSpecial()
    {
        return $this->getStartPriceMode() == self::PRICE_SPECIAL;
    }

    public function isStartPriceModeAttribute()
    {
        return $this->getStartPriceMode() == self::PRICE_ATTRIBUTE;
    }

    public function getStartPriceCoefficient()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('start_price_coefficient');
    }

    public function getStartPriceSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode'      => $this->getStartPriceMode(),
            'coefficient' => $this->getStartPriceCoefficient(),
            'attribute' => $this->getData('start_price_custom_attribute')
        );
    }

    public function getStartPriceAttributes()
    {
        $attributes = array();
        $src = $this->getStartPriceSource();

        if ($src['mode'] == self::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getReservePriceMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('reserve_price_mode');
    }

    public function isReservePriceModeNone()
    {
        return $this->getReservePriceMode() == self::PRICE_NONE;
    }

    public function isReservePriceModeProduct()
    {
        return $this->getReservePriceMode() == self::PRICE_PRODUCT;
    }

    public function isReservePriceModeSpecial()
    {
        return $this->getReservePriceMode() == self::PRICE_SPECIAL;
    }

    public function isReservePriceModeAttribute()
    {
        return $this->getReservePriceMode() == self::PRICE_ATTRIBUTE;
    }

    public function getReservePriceCoefficient()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('reserve_price_coefficient');
    }

    public function getReservePriceSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode'      => $this->getReservePriceMode(),
            'coefficient' => $this->getReservePriceCoefficient(),
            'attribute' => $this->getData('reserve_price_custom_attribute')
        );
    }

    public function getReservePriceAttributes()
    {
        $attributes = array();
        $src = $this->getReservePriceSource();

        if ($src['mode'] == self::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getPriceVariationMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('price_variation_mode');
    }

    public function isPriceVariationModeParent()
    {
        return $this->getPriceVariationMode() == self::PRICE_VARIATION_MODE_PARENT;
    }

    public function isPriceVariationModeChildren()
    {
        return $this->getPriceVariationMode() == self::PRICE_VARIATION_MODE_CHILDREN;
    }

    //-------------------------

    public function getBuyItNowPriceMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('buyitnow_price_mode');
    }

    public function isBuyItNowPriceModeNone()
    {
        return $this->getBuyItNowPriceMode() == self::PRICE_NONE;
    }

    public function isBuyItNowPriceModeProduct()
    {
        return $this->getBuyItNowPriceMode() == self::PRICE_PRODUCT;
    }

    public function isBuyItNowPriceModeSpecial()
    {
        return $this->getBuyItNowPriceMode() == self::PRICE_SPECIAL;
    }

    public function isBuyItNowPriceModeAttribute()
    {
        return $this->getBuyItNowPriceMode() == self::PRICE_ATTRIBUTE;
    }

    public function getBuyItNowPriceCoefficient()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('buyitnow_price_coefficient');
    }

    public function getBuyItNowPriceSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode'      => $this->getBuyItNowPriceMode(),
            'coefficient' => $this->getBuyItNowPriceCoefficient(),
            'attribute' => $this->getData('buyitnow_price_custom_attribute')
        );
    }

    public function getBuyItNowPriceAttributes()
    {
        $attributes = array();
        $src = $this->getBuyItNowPriceSource();

        if ($src['mode'] == self::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getBestOfferAcceptMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('best_offer_accept_mode');
    }

    public function isBestOfferAcceptModeNo()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_NO;
    }

    public function isBestOfferAcceptModePercentage()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_PERCENTAGE;
    }

    public function isBestOfferAcceptModeAttribute()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE;
    }

    public function getBestOfferAcceptValue()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('best_offer_accept_value');
    }

    public function getBestOfferAcceptSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode' => $this->getBestOfferAcceptMode(),
            'value' => $this->getBestOfferAcceptValue(),
            'attribute' => $this->getData('best_offer_accept_attribute')
        );
    }

    public function getBestOfferAcceptAttributes()
    {
        $attributes = array();
        $src = $this->getBestOfferAcceptSource();

        if ($src['mode'] == self::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getBestOfferRejectMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('best_offer_reject_mode');
    }

    public function isBestOfferRejectModeNo()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_NO;
    }

    public function isBestOfferRejectModePercentage()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_PERCENTAGE;
    }

    public function isBestOfferRejectModeAttribute()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_ATTRIBUTE;
    }

    public function getBestOfferRejectValue()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('best_offer_reject_value');
    }

    public function getBestOfferRejectSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode' => $this->getBestOfferRejectMode(),
            'value' => $this->getBestOfferRejectValue(),
            'attribute' => $this->getData('best_offer_reject_attribute')
        );
    }

    public function getBestOfferRejectAttributes()
    {
        $attributes = array();
        $src = $this->getBestOfferRejectSource();

        if ($src['mode'] == self::BEST_OFFER_REJECT_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getUsedAttributes()
    {
        return array_unique(array_merge(
            //$this->getListingTypeAttributes()
            //$this->getDurationAttributes()
            $this->getQtyAttributes(),
            $this->getStartPriceAttributes(),
            $this->getReservePriceAttributes(),
            $this->getBuyItNowPriceAttributes()
            //$this->getBestOfferAcceptAttributes()
            //$this->getBestOfferRejectAttributes()
        ));
    }

    // #######################################
}