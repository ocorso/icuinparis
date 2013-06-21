<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_ListingsCategories extends Mage_Core_Model_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listings
     */
    private $_listingModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ListingsCategories');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_ListingsCategories
     */
    public function loadInstance($id)
    {
        $this->load($id);
        
        if (is_null($this->getId())) {
             throw new Exception('Listing category does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingId
     * @return Ess_M2ePro_Model_ListingsCategories
     */
    public function loadByListingTemplate($listingId)
    {
        $this->load($listingId,'listing_id');

        if (is_null($this->getId())) {
             throw new Exception('Listing category does not exist. Probably it was deleted.');
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

        $this->_listingModel = NULL;

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Listings
     */
    public function getListing()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if (is_null($this->_listingModel)) {
            $this->_listingModel = Mage::getModel('M2ePro/Listings')->loadInstance($this->getData('listing_id'));
        }

        return $this->_listingModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_Listings $instance
     * @return void
     */
    public function setListing(Ess_M2ePro_Model_Listings $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $this->_listingModel = $instance;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_SellingFormatTemplates
     */
    public function getSellingFormatTemplate()
    {
        return $this->getListing()->getSellingFormatTemplate();
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function getListingTemplate()
    {
        return $this->getListing()->getListingTemplate();
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_DescriptionsTemplates
     */
    public function getDescriptionTemplate()
    {
        return $this->getListing()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_SynchronizationsTemplates
     */
    public function getSynchronizationTemplate()
    {
        return $this->getListing()->getSynchronizationTemplate();
    }

    // ########################################

    public function getListingId()
    {
        return (int)$this->getData('listing_id');
    }

    public function getCategoryId()
    {
        return (int)$this->getData('category_id');
    }

    // ########################################
}