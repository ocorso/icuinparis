<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping extends Mage_Core_Model_Abstract
{
    const MEASUREMENT_SYSTEM_ENGLISH = 1;
    const MEASUREMENT_SYSTEM_METRIC  = 2;

    const EBAY_MEASUREMENT_SYSTEM_ENGLISH = 'English';
    const EBAY_MEASUREMENT_SYSTEM_METRIC  = 'Metric';

    const PACKAGE_SIZE_EBAY             = 1;
    const PACKAGE_SIZE_CUSTOM_ATTRIBUTE = 2;

    const DIMENSIONS_NONE               = 0;
    const DIMENSIONS_CUSTOM_VALUE       = 1;
    const DIMENSIONS_CUSTOM_ATTRIBUTE   = 2;

    const WEIGHT_NONE                   = 0;
    const WEIGHT_CUSTOM_VALUE           = 1;
    const WEIGHT_CUSTOM_ATTRIBUTE       = 2;
    
    const HANDLING_NONE             = 0;
    const HANDLING_CUSTOM_VALUE     = 1;
    const HANDLING_CUSTOM_ATTRIBUTE = 2;

    // ########################################

    /**
     *
     * @var Ess_M2ePro_Model_ListingsTemplates
     */
    private $_listingTemplateModel = NULL;

    // ########################################
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ListingsTemplatesCalculatedShipping');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Calculated shipping does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingTemplateId
     * @return Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping
     */
    public function loadByListingTemplate($listingTemplateId)
    {
        $this->load($listingTemplateId,'listing_template_id');

        if (is_null($this->getId())) {
             throw new Exception('Calculated shipping does not exist. Probably it was deleted.');
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

        $this->delete();
        return true;
    }

    // ########################################

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

    public function getPostalCode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('originating_postal_code');
    }

    //------------------------------------------
    
    public function getMeasurementSystem()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return (int)$this->getData('measurement_system');
    }

    public function isMeasurementSystemMetric()
    {
        return $this->getMeasurementSystem() == self::MEASUREMENT_SYSTEM_METRIC;
    }

    public function isMeasurementSystemEnglish()
    {
        return $this->getMeasurementSystem() == self::MEASUREMENT_SYSTEM_ENGLISH;
    }

    //------------------------------------------

    public function getPackageSizeSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode'      => $this->getData('package_size_mode'),
            'value'     => $this->getData('package_size_ebay'),
            'attribute' => $this->getData('package_size_attribute')
        );
    }

    public function getDimensionsSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode' => $this->getData('dimension_mode'),

            'width_value'  => $this->getData('dimension_width'),
            'height_value' => $this->getData('dimension_height'),
            'depth_value'  => $this->getData('dimension_depth'),

            'width_attribute'  => $this->getData('dimension_width_attribute'),
            'height_attribute' => $this->getData('dimension_height_attribute'),
            'depth_attribute'  => $this->getData('dimension_depth_attribute')
        );
    }

    public function getWeightSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode' => $this->getData('weight_mode'),
            'weight_major' => $this->getData('weight_major'),
            'weight_minor' => $this->getData('weight_minor'),
            'weight_attribute' => $this->getData('weight_attribute')
        );
    }

    //------------------------------------------

    public function getLocalHandlingSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode'      => $this->getData('local_handling_cost_mode'),
            'value'     => $this->getData('local_handling_cost_value'),
            'attribute' => $this->getData('local_handling_cost_attribute')
        );
    }

    public function getInternationalHandlingSource()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return array(
            'mode'      => $this->getData('international_handling_cost_mode'),
            'value'     => $this->getData('international_handling_cost_value'),
            'attribute' => $this->getData('international_handling_cost_value_attribute')
        );
    }

    // ########################################
}