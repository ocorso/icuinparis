<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_ListingsTemplatesSpecifics extends Mage_Core_Model_Abstract
{
    const MODE_ITEM_SPECIFICS = 1;
    const MODE_ATTRIBUTE_SET = 2;

    const VALUE_MODE_NONE = 0;
    const VALUE_MODE_EBAY_RECOMMENDED = 1;
    const VALUE_MODE_CUSTOM_VALUE = 2;
    const VALUE_MODE_CUSTOM_ATTRIBUTE = 3;

    const RENDER_TYPE_TEXT = 'text';
    const RENDER_TYPE_SELECT_ONE = 'select_one';
    const RENDER_TYPE_SELECT_MULTIPLE = 'select_multiple';
    const RENDER_TYPE_SELECT_ONE_OR_TEXT = 'select_one_or_text';
    const RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT = 'select_multiple_or_text';

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
        $this->_init('M2ePro/ListingsTemplatesSpecifics');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_ListingsTemplatesSpecifics
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Specific does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingTemplateId
     * @return Ess_M2ePro_Model_ListingsTemplatesSpecifics
     */
    public function loadByListingTemplate($listingTemplateId)
    {
        $this->load($listingTemplateId,'listing_template_id');

        if (is_null($this->getId())) {
             throw new Exception('Specific does not exist. Probably it was deleted.');
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

    public function getMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('mode');
    }

    public function isItemSpecificsMode()
    {
        return $this->getMode() == self::MODE_ITEM_SPECIFICS;
    }

    public function isAttributeSetMode()
    {
        return $this->getMode() == self::MODE_ATTRIBUTE_SET;
    }

    //-------------------------

    public function getValueMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('value_mode');
    }

    public function isNoneValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_NONE;
    }

    public function isEbayRecommendedValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_EBAY_RECOMMENDED;
    }

    public function isCustomValueValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_CUSTOM_VALUE;
    }

    public function isCustomAttributeValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_CUSTOM_ATTRIBUTE;
    }

    //-------------------------
    
    public function getModeRelationId()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('mode_relation_id');
    }

    public function getAttributeData()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'id' => $this->getData('attribute_id'),
            'title' => $this->getData('attribute_title')
        );
    }

    public function getValues()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $valueData = array();

        if ($this->isNoneValueMode()) {
            $valueData[] = array('id'=>'unknown','value'=>'--');
            $this->isAttributeSetMode() && $valueData[count($valueData)-1]['id'] = -10;
        }
        
        if ($this->isEbayRecommendedValueMode()) {
            $valueData = json_decode($this->getData('value_ebay_recommended'),true);
        }
        
        if ($this->isCustomValueValueMode()) {
            $valueData[] = array('id'=>'unknown','value'=>$this->getData('value_custom_value'));
            $this->isAttributeSetMode() && $valueData[count($valueData)-1]['id'] = -6;
        }

        if ($this->isCustomAttributeValueMode()) {
            $valueTemp = $this->getMagentoProduct()->getAttributeValue($this->getData('value_custom_attribute'));
            $valueData[] = array('id'=>'unknown','value'=>$valueTemp);
            $this->isAttributeSetMode() && $valueData[count($valueData)-1]['id'] = -6;
        }

        return $valueData;
    }

    // ########################################
}