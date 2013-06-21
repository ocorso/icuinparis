<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_ListingsTemplatesShippings extends Mage_Core_Model_Abstract
{
    const TYPE_LOCAL         = 0;
    const TYPE_INTERNATIONAL = 1;

    const SHIPPING_FREE             = 0;
    const SHIPPING_CUSTOM_VALUE     = 1;
    const SHIPPING_CUSTOM_ATTRIBUTE = 2;

    // ########################################

    /**
     *
     * @var Ess_M2ePro_Model_ListingsTemplates
     */
    private $_listingTemplateModel = NULL;
    
    /**
     *
     * @var Ess_M2ePro_Model_MagentoProduct
     */
    protected $_magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ListingsTemplatesShippings');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_ListingsTemplatesShippings
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Shipping does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingTemplateId
     * @return Ess_M2ePro_Model_ListingsTemplatesShippings
     */
    public function loadByListingTemplate($listingTemplateId)
    {
        $this->load($listingTemplateId,'listing_template_id');

        if (is_null($this->getId())) {
             throw new Exception('Shipping does not exist. Probably it was deleted.');
        }

        return $this;
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

        return false;
    }

    /**
     * @return bool
     */
    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->_listingTemplateModel = NULL;
        $this->_magentoProductModel = NULL;

        $this->delete();
        return true;
    }

    // ########################################

    /**
     *
     * @return Ess_M2ePro_Model_MagentoProduct
     */
    public function getMagentoProduct()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        if (is_null($this->_magentoProductModel)) {
             throw new Exception('Set MagentoProduct instance first');
        }

        return $this->_magentoProductModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_MagentoProduct $instance
     * @return void
     */
    public function setMagentoProduct(Ess_M2ePro_Model_MagentoProduct $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $this->_magentoProductModel = $instance;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function getListingTemplate()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        if (is_null($this->_listingTemplateModel)) {
            $this->_listingTemplateModel = Mage::getModel('M2ePro/ListingsTemplates')
                                            ->loadInstance($this->getData('listing_template_id'));
        }

        return $this->_listingTemplateModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_ListingsTemplates $instance
     * @return void
     */
    public function setListingTemplate(Ess_M2ePro_Model_ListingsTemplates $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $this->_listingTemplateModel = $instance;
    }

    // ########################################

    public function getShippingType()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('shipping_type');
    }

    public function isShippingTypeLocal()
    {
        return $this->getShippingType() == self::TYPE_LOCAL;
    }

    public function isShippingTypeInternational()
    {
        return $this->getShippingType() == self::TYPE_INTERNATIONAL;
    }

    //-----------------------------------------

    public function getShippingValue()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('shipping_value');
    }

    public function getPriority()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('priority');
    }

    public function getLocations()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return json_decode($this->getData('locations'),true);
    }

    //-----------------------------------------

    public function getCostMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('cost_mode');
    }

    public function getCost()
    {
        $result = 0;
        
        switch ($this->getCostMode()) {
            case self::SHIPPING_FREE:
                $result = 0;
                break;
            case self::SHIPPING_CUSTOM_VALUE:
                $result = $this->getData('cost_value');
                break;
            case self::SHIPPING_CUSTOM_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue($this->getData('cost_value'));
                break;
        }

        is_string($result) && $result = str_replace(',','.',$result);
        
        return $result;
    }

    public function getCostAdditional()
    {
        $result = 0;

        switch ($this->getCostMode()) {
            case self::SHIPPING_FREE:
                $result = 0;
                break;
            case self::SHIPPING_CUSTOM_VALUE:
                $result = $this->getData('cost_additional_items');
                break;
            case self::SHIPPING_CUSTOM_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue($this->getData('cost_additional_items'));
                break;
        }

        is_string($result) && $result = str_replace(',','.',$result);

        return $result;
    }

    public function isCostModeFree()
    {
        return $this->getCostMode() == self::SHIPPING_FREE;
    }

    // ########################################
}