<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */
 
class Ess_M2ePro_Model_ListingsTemplatesPayments extends Mage_Core_Model_Abstract
{
    /**
     *
     * @var Ess_M2ePro_Model_ListingsTemplates
     */
    private $_listingTemplateModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ListingsTemplatesPayments');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_ListingsTemplatesPayments
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Payment does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingTemplateId
     * @return Ess_M2ePro_Model_ListingsTemplatesPayments
     */
    public function loadByListingTemplate($listingTemplateId)
    {
        $this->load($listingTemplateId,'listing_template_id');

        if (is_null($this->getId())) {
             throw new Exception('Payment does not exist. Probably it was deleted.');
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
}