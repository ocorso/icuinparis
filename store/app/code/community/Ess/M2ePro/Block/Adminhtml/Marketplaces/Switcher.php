<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Marketplaces_Switcher extends Mage_Adminhtml_Block_Template
{
    /**
     * @var array
     */
    protected $_marketplacesIds = null;

    protected $_marketplaceVarName = 'marketplace';

    /**
     * @var bool
     */
    protected $_hasDefaultOption = true;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/marketplaces/switcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultMarketplaceName($this->__('All Marketplaces'));
    }

    /**
     * Get accounts
     *
     * @return array
     */
    public function getMarketplaces()
    {
        $collection = Mage::getModel('M2ePro/Marketplaces')->getCollection();
        if (!is_null($this->_marketplacesIds) && count($this->_marketplacesIds) != 0) {
            $collection->getSelect()->where('`id` IN ('.implode(',',$this->_marketplacesIds).')');
        }

        return $collection->getItems();
    }

    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current' => true, $this->_marketplaceVarName => null));
    }

    public function setMarketplaceVarName($varName)
    {
        $this->_marketplaceVarName = $varName;
        return $this;
    }

    public function getMarketplaceId()
    {
        return $this->getRequest()->getParam($this->_marketplaceVarName);
    }

    public function setMarketplacesIds($marketplacesIds)
    {
        $this->_marketplacesIds = $marketplacesIds;
        return $this;
    }

    public function getMarketplacesIds()
    {
        return $this->_marketplacesIds;
    }

    public function isShow()
    {
        return true;
    }

    protected function _toHtml()
    {
//        if (!Mage::getModel('M2ePro/Accounts')->isSingleAccountMode()) {
            return parent::_toHtml();
//        }
//        return '';
    }

    /**
     * Set/Get whether the switcher should show default option
     *
     * @param bool $hasDefaultOption
     * @return bool
     */
    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }
        return $this->_hasDefaultOption;
    }
}