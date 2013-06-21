<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_TemplatesAttributeSets extends Mage_Core_Model_Abstract
{
    const TEMPLATE_TYPE_SELLING_FORMAT = 1;
    const TEMPLATE_TYPE_DESCRIPTION = 2;
    const TEMPLATE_TYPE_LISTING = 3;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/TemplatesAttributeSets');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_TemplatesAttributeSets
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Attribute set does not exist. Probably it was deleted.');
        }

        return $this;
    }

    // ########################################

    /**
     * @return bool
     */
    public function isLocked()
    {
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

        $this->delete();
        return true;
    }

    // ########################################

    public function isSellingFormatTemplate()
    {
        return $this->getTemplateType() == self::TEMPLATE_TYPE_SELLING_FORMAT;
    }

    public function isDescriptionTemplate()
    {
        return $this->getTemplateType() == self::TEMPLATE_TYPE_DESCRIPTION;
    }

    public function isListingTemplate()
    {
        return $this->getTemplateType() == self::TEMPLATE_TYPE_LISTING;
    }

    // ########################################

    public function getTemplateType()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('template_type');
    }

    public function getTemplateId()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('template_id');
    }

    public function getAttributeSetId()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('attribute_set_id');
    }

    // ########################################
}