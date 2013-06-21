<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_ListingsTemplates extends Mage_Core_Model_Abstract
{
    const CATEGORIES_MODE_EBAY      = 0;
    const CATEGORIES_MODE_ATTRIBUTE = 1;

    const STORE_CATEGORY_NONE             = 0;
    const STORE_CATEGORY_EBAY_VALUE       = 1;
    const STORE_CATEGORY_CUSTOM_ATTRIBUTE = 2;

    const SKU_MODE_NO  = 0;
    const SKU_MODE_YES = 1;

	const SHIPPING_TYPE_FLAT                = 0;
    const SHIPPING_TYPE_CALCULATED          = 1;
    const SHIPPING_TYPE_FREIGHT             = 2;
	const SHIPPING_TYPE_LOCAL               = 3;
	const SHIPPING_TYPE_NO_INTERNATIONAL    = 4;

    const EBAY_SHIPPING_TYPE_FLAT = "flat";
    const EBAY_SHIPPING_TYPE_CALCULATED = "calculated";
    const EBAY_SHIPPING_TYPE_FREIGHT = "freight";
    const EBAY_SHIPPING_TYPE_LOCAL = "local";

    const GALLERY_TYPE_NO       = 0;
    const GALLERY_TYPE_PICTURE  = 1;
    const GALLERY_TYPE_PLUS     = 2;
    const GALLERY_TYPE_FEATURED = 3;

    const VARIATION_DISABLED = 0;
    const VARIATION_ENABLED  = 1;

    const VARIATION_IGNORE_DISABLED = 0;
    const VARIATION_IGNORE_ENABLED  = 1;

	const GET_IT_FAST_DISABLED     = 0;
    const GET_IT_FAST_ENABLED      = 1;

    const OPTION_NONE               = 0;
    const OPTION_CUSTOM_VALUE       = 1;
	const OPTION_CUSTOM_ATTRIBUTE   = 2;

    const PRODUCT_DETAIL_MODE_NONE = 0;
    const PRODUCT_DETAIL_MODE_CUSTOM_VALUE = 1;
    const PRODUCT_DETAIL_MODE_CUSTOM_ATTRIBUTE = 2;
    
    // ########################################

    /**
     * @var Ess_M2ePro_Model_Accounts
     */
    private $_accountModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Marketplaces
     */
    private $_marketplaceModel = NULL;
    
    /**
     * @var Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping
     */
    private $_calculatedShippingModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ListingsTemplates');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('General template does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $accountId
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function loadByAccount($accountId)
    {
        $this->load($accountId,'account_id');

        if (is_null($this->getId())) {
             throw new Exception('General template does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $marketplaceId
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function loadByMarketplace($marketplaceId)
    {
        $this->load($marketplaceId,'marketplace_id');

        if (is_null($this->getId())) {
             throw new Exception('General template does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingId
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function loadByListing($listingId)
    {
         $tempModel = Mage::getModel('M2ePro/Listings')->load($listingId);

         if (is_null($tempModel->getId())) {
             throw new Exception('Listing does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('listing_template_id'));
    }

    /**
     * @throws LogicException
     * @param  int $listingTemplatePaymentId
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function loadByListingTemplatePayment($listingTemplatePaymentId)
    {
         $tempModel = Mage::getModel('M2ePro/ListingsTemplatesPayments')->load($listingTemplatePaymentId);

         if (is_null($tempModel->getId())) {
             throw new Exception('General template payment does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('listing_template_id'));
    }

    /**
     * @throws LogicException
     * @param  int $listingTemplateShippingId
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function loadByListingTemplateShipping($listingTemplateShippingId)
    {
         $tempModel = Mage::getModel('M2ePro/ListingsTemplatesShippings')->load($listingTemplateShippingId);

         if (is_null($tempModel->getId())) {
             throw new Exception('General template shipping does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('listing_template_id'));
    }

    /**
     * @throws LogicException
     * @param  int $listingTemplateSpecificId
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function loadByListingTemplateSpecific($listingTemplateSpecificId)
    {
         $tempModel = Mage::getModel('M2ePro/ListingsTemplatesSpecifics')->load($listingTemplateSpecificId);

         if (is_null($tempModel->getId())) {
             throw new Exception('General template specific does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('listing_template_id'));
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
                                ->addFieldToFilter('listing_template_id', $this->getId())
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

        $this->getCalculatedShipping()->deleteInstance();

        $listingsTemplatesPayments = $this->getListingsTemplatesPayments(true);
        foreach ($listingsTemplatesPayments as $listingTemplatePayment) {
            $listingTemplatePayment->deleteInstance();
        }

        $listingsTemplatesShippings = $this->getListingsTemplatesShippings(true);
        foreach ($listingsTemplatesShippings as $listingTemplateShipping) {
            $listingTemplateShipping->deleteInstance();
        }

        $listingsTemplatesSpecifics = $this->getListingsTemplatesSpecifics(true);
        foreach ($listingsTemplatesSpecifics as $listingTemplateSpecific) {
            $listingTemplateSpecific->deleteInstance();
        }

        $this->_accountModel = NULL;
        $this->_marketplaceModel = NULL;
        $this->_calculatedShippingModel = NULL;

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Accounts
     */
    public function getAccount()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        if (is_null($this->_accountModel)) {
            $this->_accountModel = Mage::getModel('M2ePro/Accounts')
                 ->loadInstance($this->getData('account_id'));
        }

        return $this->_accountModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_Accounts $instance
     * @return void
     */
    public function setAccount(Ess_M2ePro_Model_Accounts $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $this->_accountModel = $instance;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Marketplaces
     */
    public function getMarketplace()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        if (is_null($this->_marketplaceModel)) {
            $this->_marketplaceModel = Mage::getModel('M2ePro/Marketplaces')
                 ->loadInstance($this->getData('marketplace_id'));
        }

        return $this->_marketplaceModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_Marketplaces $instance
     * @return void
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplaces $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $this->_marketplaceModel = $instance;
    }
    
    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping
     */
    public function getCalculatedShipping()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        if (is_null($this->_calculatedShippingModel)) {
            $this->_calculatedShippingModel = Mage::getModel('M2ePro/ListingsTemplatesCalculatedShipping')->load($this->getId());
        }

        return $this->_calculatedShippingModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping $instance
     * @return void
     */
    public function setCalculatedShipping(Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $this->_calculatedShippingModel = $instance;
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
        $tempCollection->addFieldToFilter('listing_template_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $tempInstance = Mage::getModel('M2ePro/Listings')
                                        ->loadInstance($item['id']);
                $tempInstance->setListingTemplate($this);
                $resultArray[] = $tempInstance;
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @param array $filters
     * @return array
     */
    public function getListingsTemplatesPayments($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/ListingsTemplatesPayments')->getCollection();
        $tempCollection->addFieldToFilter('listing_template_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $resultArray[] = Mage::getModel('M2ePro/ListingsTemplatesPayments')
                                        ->loadInstance($item['id']);
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @param array $filters
     * @return array
     */
    public function getListingsTemplatesShippings($asObjects = false, array $filters = array(), $sortPosition = Varien_Data_Collection::SORT_ORDER_ASC)
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/ListingsTemplatesShippings')->getCollection();
        $tempCollection->addFieldToFilter('listing_template_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        if ($sortPosition !== false) {
            $tempCollection->setOrder('priority',$sortPosition);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $resultArray[] = Mage::getModel('M2ePro/ListingsTemplatesShippings')
                                        ->loadInstance($item['id']);
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @param array $filters
     * @return array
     */
    public function getListingsTemplatesSpecifics($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/ListingsTemplatesSpecifics')->getCollection();
        $tempCollection->addFieldToFilter('listing_template_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $resultArray[] = Mage::getModel('M2ePro/ListingsTemplatesSpecifics')
                                        ->loadInstance($item['id']);
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    // ########################################

    public function getTitle()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('title');
    }

    public function getGalleryType()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('gallery_type');
    }

    public function isSkuEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('sku_mode') == self::SKU_MODE_YES;
    }

    public function getEnhancements()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('enhancement') ? explode(',', $this->getData('enhancement')) : array();
    }

    public function getRefundOptions()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'accepted'     => $this->getData('refund_accepted'),
            'option'       => $this->getData('refund_option'),
            'within'       => $this->getData('refund_within'),
            'description'  => $this->getData('refund_description'),
            'shippingcost' => $this->getData('refund_shippingcost')
        );
    }

    //-------------------------------

    public function getCategoriesSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode'                => $this->getData('categories_mode'),
            'main_value'          => $this->getData('categories_main_id'),
            'main_attribute'      => $this->getData('categories_main_attribute'),
            'secondary_value'     => $this->getData('categories_secondary_id'),
            'secondary_attribute' => $this->getData('categories_secondary_attribute')
        );
    }

    public function getStoreCategoriesSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'main_mode'       => $this->getData('store_categories_main_mode'),
            'main_value'      => $this->getData('store_categories_main_id'),
            'main_attribute'  => $this->getData('store_categories_main_attribute'),
            'secondary_mode'      => $this->getData('store_categories_secondary_mode'),
            'secondary_value'     => $this->getData('store_categories_secondary_id'),
            'secondary_attribute' => $this->getData('store_categories_secondary_attribute'),
        );
    }

    //-------------------------------

    public function isVariationEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('variation_enabled') == self::VARIATION_ENABLED;
    }

    public function isVariationIgnore()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('variation_ignore') == self::VARIATION_IGNORE_ENABLED;
    }

    public function isVariationMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->isVariationEnabled() && !$this->isVariationIgnore();
    }

    //-------------------------------

    public function getItemConditionSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode'      => $this->getData('categories_mode'),
            'value'     => $this->getData('condition_value'),
            'attribute' => $this->getData('condition_attribute')
        );
    }

    public function getProductDetailSource($type)
    {
        if (!in_array($type, array('isbn', 'epid', 'upc', 'ean'))) {
            throw new InvalidArgumentException('Unknown product details name');
        }

        if ($this->getData('product_details') == '' || $this->getData('product_details') == json_encode(array())) {
            return NULL;
        }

        $tempProductsDetails = json_decode($this->getData('product_details'),true);
        
        if (!isset($tempProductsDetails["product_details_{$type}_mode"]) ||
            !isset($tempProductsDetails["product_details_{$type}_cv"]) ||
            !isset($tempProductsDetails["product_details_{$type}_ca"])) {
            return NULL;
        }

        return array(
            'mode'      => $tempProductsDetails["product_details_{$type}_mode"],
            'value'     => $tempProductsDetails["product_details_{$type}_cv"],
            'attribute' => $tempProductsDetails["product_details_{$type}_ca"]
        );
    }

    //-------------------------------

    public function getCountry()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('country');
    }

    public function getPostalCode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('postal_code');
    }
    
    public function getAddress()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('address');
    }

    public function isGetItFastEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('get_it_fast') == self::GET_IT_FAST_ENABLED;
    }

    public function getDispatchTime()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('dispatch_time');
    }

    //-------------------------------

    public function isLocalShippingEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return true;
    }

    public function isLocalShippingFlatEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('local_shipping_mode') == self::SHIPPING_TYPE_FLAT;
    }

    public function isLocalShippingCalculatedEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('local_shipping_mode') == self::SHIPPING_TYPE_CALCULATED;
    }

    public function isLocalShippingFreightEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('local_shipping_mode') == self::SHIPPING_TYPE_FREIGHT;
    }

    public function isLocalShippingLocalEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('local_shipping_mode') == self::SHIPPING_TYPE_LOCAL;
    }

    //-------------------------------
    
    public function isInternationalShippingEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('international_shipping_mode') != self::SHIPPING_TYPE_NO_INTERNATIONAL;
    }

    public function isInternationalShippingFlatEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('international_shipping_mode') == self::SHIPPING_TYPE_FLAT;
    }

    public function isInternationalShippingCalculatedEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('international_shipping_mode') == self::SHIPPING_TYPE_CALCULATED;
    }
    
    //-------------------------------

    public function isLocalShippingDiscountEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (bool)$this->getData('local_shipping_discount_mode');
    }

    public function isInternationalShippingDiscountEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (bool)$this->getData('international_shipping_discount_mode');
    }

    //-------------------------------

    public function isUseEbayTaxTableEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (bool)$this->getData('use_ebay_tax_table');
    }

    public function getVatPercent()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (float)$this->getData('vat_percent');
    }

    public function isUseEbayLocalShippingRateTableEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (float)$this->getData('use_ebay_local_shipping_rate_table');
    }

    //-------------------------------

    public function getPaymentMethods()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $payments = Mage::getModel('M2ePro/ListingsTemplatesPayments')->getCollection()->addFieldToFilter('listing_template_id', $this->getId())->toArray();
        $return = array();
        foreach ($payments['items'] as $payment) {
            $return[] = $payment['payment_id'];
        }

        return $return;
    }

    public function getPayPalEmailAddress()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('pay_pal_email_address');
    }

    public function isPayPalImmediatePaymentEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (bool)$this->getData('pay_pal_immediate_payment');
    }

    //-------------------------------

    public function getUsedAttributes()
    {
        //return array_unique(array_merge());
        return array();
    }
    
    // ########################################
}